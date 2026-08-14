#!/usr/bin/env python3
"""
Convert a SaiFlower MySQL/MariaDB dump to PostgreSQL SQL.
Preserves table names, integer IDs, indexes, FKs, and row data.
"""

from __future__ import annotations

import argparse
import re
import sys
from dataclasses import dataclass, field
from pathlib import Path
from typing import Optional

OUT_DIR = Path(__file__).resolve().parent

# ---------------------------------------------------------------------------
# Metadata from dump ALTER TABLE / AUTO_INCREMENT / CONSTRAINT sections
# ---------------------------------------------------------------------------

AUTO_INCREMENT: dict[str, int] = {
    "addons": 15,
    "admin_tokens": 53,
    "admin_users": 2,
    "blogs": 168,
    "cakes": 6,
    "cake_variants": 10,
    "categories": 23,
    "comments": 13,
    "customers": 23,
    "customer_addresses": 1,
    "dynamic_pages": 211,
    "events": 8,
    "faqs": 16,
    "flowers": 322,
    "flower_images": 53,
    "flower_variants": 18,
    "gallery": 4,
    "gifts": 4,
    "gift_variants": 2,
    "homepage_circles": 10,
    "homepage_sections": 36,
    "homepage_section_items": 132,
    "homepage_slides": 16,
    "leads": 111,
    "occasions": 5,
    "orders": 37,
    "pricing_log": 9,
    "products": 7,
    "product_occasions": 269,
    "promo_codes": 24,
    "reviews": 4,
    "seo_meta": 5,
    "settings": 2,
    "tags": 66,
    "wishlist": 14,
    # global_pricing has PK but no AUTO_INCREMENT in dump — still use identity
}

PRIMARY_KEYS: dict[str, list[str]] = {t: ["id"] for t in [
    "addons", "admin_tokens", "admin_users", "blogs", "cakes", "cake_variants",
    "categories", "comments", "customers", "customer_addresses", "dynamic_pages", "events", "faqs",
    "flowers", "flower_images", "flower_variants", "gallery", "gifts",
    "gift_variants", "global_pricing", "homepage_circles", "homepage_sections",
    "homepage_section_items", "homepage_slides", "leads", "occasions", "orders",
    "pricing_log", "products", "product_occasions", "promo_codes", "reviews",
    "seo_meta", "settings", "tags", "wishlist",
]}

# Constraint names must be unique across the PostgreSQL schema (unlike MySQL).
UNIQUE_KEYS: list[tuple[str, str, list[str]]] = [
    ("admin_users", "admin_users_username_key", ["username"]),
    ("blogs", "blogs_slug_key", ["slug"]),
    ("cakes", "cakes_slug_key", ["slug"]),
    ("customers", "customers_email_key", ["email"]),
    ("customers", "uniq_customers_google_id", ["google_id"]),
    ("dynamic_pages", "dynamic_pages_slug_key", ["slug"]),
    ("events", "events_slug_key", ["slug"]),
    ("gifts", "gifts_slug_key", ["slug"]),
    ("occasions", "occasions_name_key", ["name"]),
    ("product_occasions", "unique_product_occasion", ["product_type", "product_id", "occasion_name"]),
    ("promo_codes", "promo_codes_code_key", ["code"]),
    ("seo_meta", "seo_meta_page_identifier_key", ["page_identifier"]),
    ("wishlist", "unique_wishlist", ["user_id", "product_id", "type"]),
]

INDEXES: list[tuple[str, str, list[str]]] = [
    ("admin_tokens", "admin_tokens_admin_id_idx", ["admin_id"]),
    ("admin_tokens", "admin_tokens_token_idx", ["token"]),
    ("cake_variants", "cake_variants_cake_id_idx", ["cake_id"]),
    ("customer_addresses", "customer_addresses_customer_id_idx", ["customer_id"]),
    ("flower_images", "flower_images_flower_id_idx", ["flower_id"]),
    ("flower_variants", "flower_variants_flower_id_idx", ["flower_id"]),
    ("gift_variants", "gift_variants_gift_id_idx", ["gift_id"]),
    ("homepage_section_items", "homepage_section_items_section_id_idx", ["section_id"]),
    ("product_occasions", "idx_occasion", ["occasion_name"]),
]

FOREIGN_KEYS: list[tuple[str, str, str, str, str, Optional[str]]] = [
    # (table, constraint, column, ref_table, ref_column, on_delete)
    ("cake_variants", "cake_variants_ibfk_1", "cake_id", "cakes", "id", "CASCADE"),
    ("customer_addresses", "customer_addresses_customer_id_fkey", "customer_id", "customers", "id", "CASCADE"),
    ("gift_variants", "gift_variants_ibfk_1", "gift_id", "gifts", "id", "CASCADE"),
    ("homepage_section_items", "homepage_section_items_ibfk_1", "section_id", "homepage_sections", "id", "CASCADE"),
    # Indexed in MySQL without formal FK — enforced in Postgres for Prisma
    ("flower_images", "flower_images_flower_id_fkey", "flower_id", "flowers", "id", "CASCADE"),
    ("flower_variants", "flower_variants_flower_id_fkey", "flower_id", "flowers", "id", "CASCADE"),
    ("admin_tokens", "admin_tokens_admin_id_fkey", "admin_id", "admin_users", "id", "CASCADE"),
    ("wishlist", "wishlist_user_id_fkey", "user_id", "customers", "id", "CASCADE"),
]

# Kept for documentation; all soft FKs promoted into FOREIGN_KEYS above.
SOFT_RELATIONS: list[tuple[str, str, str, str]] = []

ENUM_DEFS: dict[str, list[str]] = {
    "comments_status": ["approved", "pending"],
    "orders_status": ["Pending", "Completed", "Cancelled"],
    "product_occasions_product_type": ["flower", "cake", "gift"],
    "promo_codes_discount_type": ["percentage", "flat"],
    "promo_codes_type": ["percentage", "flat"],
    "customer_address_type": ["Home", "Work", "Other"],
}


@dataclass
class Column:
    name: str
    mysql_type: str
    not_null: bool
    default: Optional[str]
    on_update: bool = False
    enum_name: Optional[str] = None
    enum_values: list[str] = field(default_factory=list)


@dataclass
class Table:
    name: str
    columns: list[Column] = field(default_factory=list)


def split_columns(body: str) -> list[str]:
    """Split CREATE TABLE body on top-level commas."""
    parts: list[str] = []
    buf: list[str] = []
    depth = 0
    in_str = False
    quote = ""
    i = 0
    while i < len(body):
        ch = body[i]
        if in_str:
            buf.append(ch)
            if ch == "\\" and i + 1 < len(body):
                buf.append(body[i + 1])
                i += 2
                continue
            if ch == quote:
                in_str = False
            i += 1
            continue
        if ch in ("'", '"'):
            in_str = True
            quote = ch
            buf.append(ch)
            i += 1
            continue
        if ch == "(":
            depth += 1
            buf.append(ch)
            i += 1
            continue
        if ch == ")":
            depth -= 1
            buf.append(ch)
            i += 1
            continue
        if ch == "," and depth == 0:
            parts.append("".join(buf).strip())
            buf = []
            i += 1
            continue
        buf.append(ch)
        i += 1
    if buf:
        parts.append("".join(buf).strip())
    return [p for p in parts if p]


def parse_column(line: str, table: str) -> Optional[Column]:
    line = line.strip().rstrip(",")
    if not line or line.upper().startswith(("PRIMARY KEY", "UNIQUE KEY", "KEY ", "CONSTRAINT", "FULLTEXT")):
        return None
    m = re.match(r"`([^`]+)`\s+(.+)$", line, re.S)
    if not m:
        return None
    name, rest = m.group(1), m.group(2).strip()

    not_null = bool(re.search(r"\bNOT NULL\b", rest, re.I))
    on_update = bool(re.search(r"ON UPDATE\s+current_timestamp\(\)", rest, re.I))

    default = None
    dm = re.search(
        r"DEFAULT\s+((?:current_timestamp\(\))|(?:NULL)|(?:'[^']*')|(?:\"[^\"]*\")|(?:-?\d+(?:\.\d+)?))",
        rest,
        re.I,
    )
    if dm:
        default = dm.group(1)

    # Strip attributes to leave type
    type_part = rest
    type_part = re.sub(r"\bNOT NULL\b", "", type_part, flags=re.I)
    type_part = re.sub(r"DEFAULT\s+(?:current_timestamp\(\)|NULL|'[^']*'|\"[^\"]*\"|-?\d+(?:\.\d+)?)", "", type_part, flags=re.I)
    type_part = re.sub(r"\bNULL\b", "", type_part, flags=re.I)
    type_part = re.sub(r"ON UPDATE\s+current_timestamp\(\)", "", type_part, flags=re.I)
    type_part = re.sub(r"\bAUTO_INCREMENT\b", "", type_part, flags=re.I)
    type_part = re.sub(r"\bCOMMENT\s+'[^']*'", "", type_part, flags=re.I)
    type_part = type_part.strip()

    enum_name = None
    enum_values: list[str] = []
    em = re.match(r"enum\((.+)\)", type_part, re.I)
    if em:
        enum_values = re.findall(r"'([^']*)'", em.group(1))
        enum_name = (
            "customer_address_type"
            if table == "customer_addresses" and name == "address_type"
            else f"{table}_{name}"
        )
        type_part = f"ENUM:{enum_name}"

    return Column(
        name=name,
        mysql_type=type_part,
        not_null=not_null,
        default=default,
        on_update=on_update,
        enum_name=enum_name,
        enum_values=enum_values,
    )


def parse_tables(dump: str) -> list[Table]:
    tables: list[Table] = []
    for m in re.finditer(
        r"CREATE TABLE `([^`]+)`\s*\((.*?)\)\s*ENGINE=",
        dump,
        re.S | re.I,
    ):
        tname = m.group(1)
        body = m.group(2)
        cols: list[Column] = []
        for part in split_columns(body):
            col = parse_column(part, tname)
            if col:
                cols.append(col)
        tables.append(Table(name=tname, columns=cols))
    return tables


def mysql_type_to_pg(col: Column) -> str:
    t = col.mysql_type.strip()
    if t.startswith("ENUM:"):
        return t.split(":", 1)[1]
    tl = t.lower()

    if tl.startswith("tinyint(1)"):
        return "smallint"
    if tl.startswith("tinyint"):
        return "smallint"
    if tl.startswith("smallint"):
        return "smallint"
    if tl.startswith("mediumint") or tl.startswith("int"):
        return "integer"
    if tl.startswith("bigint"):
        return "bigint"
    if tl.startswith("decimal") or tl.startswith("numeric"):
        mm = re.match(r"decimal\((\d+),(\d+)\)", tl)
        if mm:
            return f"numeric({mm.group(1)},{mm.group(2)})"
        return "numeric"
    if tl.startswith("float"):
        return "real"
    if tl.startswith("double"):
        return "double precision"
    if tl.startswith("datetime") or tl.startswith("timestamp"):
        return "timestamptz"
    if tl.startswith("date"):
        return "date"
    if tl.startswith("time"):
        return "time"
    if tl.startswith("longtext") or tl.startswith("mediumtext") or tl.startswith("tinytext") or tl.startswith("text"):
        return "text"
    if tl.startswith("longblob") or tl.startswith("mediumblob") or tl.startswith("blob"):
        return "bytea"
    if tl.startswith("json"):
        return "jsonb"
    if tl.startswith("varchar"):
        mm = re.match(r"varchar\((\d+)\)", tl)
        if mm:
            return f"varchar({mm.group(1)})"
        return "varchar"
    if tl.startswith("char"):
        mm = re.match(r"char\((\d+)\)", tl)
        if mm:
            return f"char({mm.group(1)})"
        return "char"
    return "text"


def convert_default(col: Column, pg_type: str) -> Optional[str]:
    if col.default is None:
        return None
    d = col.default
    if re.match(r"current_timestamp\(\)", d, re.I):
        return "CURRENT_TIMESTAMP"
    if d.upper() == "NULL":
        return "NULL"
    if d.startswith("'") or d.startswith('"'):
        # string literal — keep single quotes
        inner = d[1:-1].replace("'", "''")
        if col.enum_name:
            return f"'{inner}'::{col.enum_name}"
        return f"'{inner}'"
    # numeric
    if pg_type.startswith("numeric") or pg_type in ("integer", "smallint", "bigint", "real", "double precision"):
        return d
    return d


def quote_ident(name: str) -> str:
    return '"' + name.replace('"', '""') + '"'


def emit_schema_sql(tables: list[Table]) -> str:
    lines: list[str] = [
        "-- =============================================================================",
        "-- SaiFlower → PostgreSQL on Hostinger VPS",
        "-- Converted from MySQL dump: u977002836_Saiflower999",
        "-- Preserves: tables, PKs, unique keys, indexes, FKs, integer IDs, data semantics",
        "-- =============================================================================",
        "",
        "BEGIN;",
        "",
        "-- Enums",
    ]
    # Collect enums from columns + known defs
    enums: dict[str, list[str]] = dict(ENUM_DEFS)
    for t in tables:
        for c in t.columns:
            if c.enum_name and c.enum_values:
                enums[c.enum_name] = c.enum_values

    for ename, values in enums.items():
        vals = ", ".join(f"'{v}'" for v in values)
        lines.append(f"CREATE TYPE {quote_ident(ename)} AS ENUM ({vals});")

    lines.append("")
    lines.append("-- Tables")

    for t in tables:
        lines.append(f"CREATE TABLE {quote_ident(t.name)} (")
        col_defs: list[str] = []
        for c in t.columns:
            pg = mysql_type_to_pg(c)
            parts = [f"  {quote_ident(c.name)} {pg}"]
            if c.not_null:
                parts.append("NOT NULL")
            default = convert_default(c, pg)
            if default is not None:
                parts.append(f"DEFAULT {default}")
            col_defs.append(" ".join(parts))
        lines.append(",\n".join(col_defs))
        lines.append(");")
        lines.append("")

    lines.append("-- Primary keys")
    for tname, cols in PRIMARY_KEYS.items():
        ccols = ", ".join(quote_ident(c) for c in cols)
        lines.append(f"ALTER TABLE {quote_ident(tname)} ADD CONSTRAINT {quote_ident(tname + '_pkey')} PRIMARY KEY ({ccols});")

    lines.append("")
    lines.append("-- Unique constraints")
    for tname, uname, cols in UNIQUE_KEYS:
        ccols = ", ".join(quote_ident(c) for c in cols)
        lines.append(f"ALTER TABLE {quote_ident(tname)} ADD CONSTRAINT {quote_ident(uname)} UNIQUE ({ccols});")

    lines.append("")
    lines.append("-- Indexes")
    for tname, iname, cols in INDEXES:
        ccols = ", ".join(quote_ident(c) for c in cols)
        lines.append(f"CREATE INDEX {quote_ident(iname)} ON {quote_ident(tname)} ({ccols});")

    lines.append("")
    lines.append("-- Foreign keys")
    for tname, cname, col, rt, rc, on_delete in FOREIGN_KEYS:
        od = f" ON DELETE {on_delete}" if on_delete else ""
        ou = " ON UPDATE CASCADE" if cname == "customer_addresses_customer_id_fkey" else ""
        lines.append(
            f"ALTER TABLE {quote_ident(tname)} ADD CONSTRAINT {quote_ident(cname)} "
            f"FOREIGN KEY ({quote_ident(col)}) REFERENCES {quote_ident(rt)} ({quote_ident(rc)})"
            f"{od}{ou} DEFERRABLE INITIALLY DEFERRED;"
        )

    lines.append("")
    lines.append("-- Identity / sequences (preserve MySQL AUTO_INCREMENT next values)")
    for tname, next_val in AUTO_INCREMENT.items():
        lines.append(
            f"ALTER TABLE {quote_ident(tname)} ALTER COLUMN {quote_ident('id')} "
            f"ADD GENERATED BY DEFAULT AS IDENTITY (INCREMENT BY 1 START WITH {next_val});"
        )
    if "global_pricing" not in AUTO_INCREMENT:
        lines.append(
            f"ALTER TABLE {quote_ident('global_pricing')} ALTER COLUMN {quote_ident('id')} "
            f"ADD GENERATED BY DEFAULT AS IDENTITY (INCREMENT BY 1 START WITH 1);"
        )

    lines.append("")
    lines.append("COMMIT;")
    lines.append("")
    return "\n".join(lines)


# ---------------------------------------------------------------------------
# INSERT conversion
# ---------------------------------------------------------------------------

def convert_insert_line(line: str, tables_by_name: dict[str, Table]) -> str:
    """Convert a MySQL INSERT statement line to PostgreSQL."""
    # Skip empty / comments
    s = line.strip()
    if not s or s.startswith("--"):
        return ""

    # REPLACE INTO → INSERT INTO
    s = re.sub(r"^REPLACE\s+INTO", "INSERT INTO", s, flags=re.I)

    # Backticks → double quotes for identifiers
    def unbacktick(m: re.Match) -> str:
        return quote_ident(m.group(1))

    s = re.sub(r"`([^`]+)`", unbacktick, s)

    # MySQL escaped quotes \' → ''
    # Careful: only inside string literals. Simple approach for dump style:
    # replace \' with '' when not already processed
    s = s.replace("\\'", "''")
    s = s.replace('\\"', '"')
    # MySQL \\ → \
    s = s.replace("\\\\", "\\")
    # MySQL \\n etc. — dump uses real newlines in strings rarely; keep \\n as \n chars if escaped
    s = s.replace("\\n", "\n").replace("\\r", "\r").replace("\\t", "\t")

    # Zero dates
    s = re.sub(r"'0000-00-00 00:00:00'", "NULL", s)
    s = re.sub(r"'0000-00-00'", "NULL", s)

    # Cast enum string literals where needed is hard line-by-line;
    # PostgreSQL accepts unknown → enum if column type is enum on INSERT.
    # Explicit cast not required when inserting into typed columns.

    return s


def find_insert_statements(dump: str) -> list[tuple[str, str, str]]:
    """
    Extract INSERT INTO `t` (cols) VALUES ...; statements with string-aware
    scanning so HTML/JS containing ');' cannot truncate the VALUES clause.
    Returns list of (table, columns_raw, values_raw).
    """
    results: list[tuple[str, str, str]] = []
    i = 0
    n = len(dump)
    insert_re = re.compile(r"INSERT\s+INTO\s+`", re.I)

    while True:
        m = insert_re.search(dump, i)
        if not m:
            break
        start = m.start()
        # Parse `table`
        j = m.end()
        end_tick = dump.find("`", j)
        if end_tick < 0:
            break
        tname = dump[j:end_tick]
        j = end_tick + 1
        # Skip to (
        while j < n and dump[j].isspace():
            j += 1
        if j >= n or dump[j] != "(":
            i = end_tick + 1
            continue
        # Columns list until matching )
        j += 1
        col_start = j
        depth = 1
        while j < n and depth:
            if dump[j] == "(":
                depth += 1
            elif dump[j] == ")":
                depth -= 1
            j += 1
        cols_raw = dump[col_start : j - 1]
        # Expect VALUES
        while j < n and dump[j].isspace():
            j += 1
        if dump[j : j + 6].upper() != "VALUES":
            i = j
            continue
        j += 6
        while j < n and dump[j].isspace():
            j += 1
        val_start = j
        # Scan until semicolon outside strings
        in_str = False
        while j < n:
            ch = dump[j]
            if in_str:
                if ch == "\\" and j + 1 < n:
                    j += 2
                    continue
                if ch == "'":
                    # MySQL '' escape inside strings
                    if j + 1 < n and dump[j + 1] == "'":
                        j += 2
                        continue
                    in_str = False
                j += 1
                continue
            if ch == "'":
                in_str = True
                j += 1
                continue
            if ch == ";":
                values = dump[val_start:j].strip()
                results.append((tname, cols_raw, values))
                i = j + 1
                break
            j += 1
        else:
            break
    return results


def convert_mysql_values(vals: str) -> str:
    vals = vals.replace("\\'", "''")
    vals = vals.replace('\\"', '"')
    vals = vals.replace("\\\\", "\x00BACKSLASH\x00")
    vals = vals.replace("\\n", "\n").replace("\\r", "\r").replace("\\t", "\t")
    vals = vals.replace("\x00BACKSLASH\x00", "\\")
    vals = re.sub(r"'0000-00-00 00:00:00'", "NULL", vals)
    vals = re.sub(r"'0000-00-00'", "NULL", vals)
    return vals


def count_value_rows(values: str) -> int:
    """Count top-level row tuples in a MySQL VALUES clause."""
    count = 0
    depth = 0
    in_str = False
    i = 0
    while i < len(values):
        ch = values[i]
        if in_str:
            if ch == "\\" and i + 1 < len(values):
                i += 2
                continue
            if ch == "'":
                if i + 1 < len(values) and values[i + 1] == "'":
                    i += 2
                    continue
                in_str = False
            i += 1
            continue
        if ch == "'":
            in_str = True
        elif ch == "(":
            if depth == 0:
                count += 1
            depth += 1
        elif ch == ")":
            depth -= 1
        i += 1
    if in_str or depth != 0:
        raise ValueError("Unbalanced string or parentheses in INSERT values")
    return count


def extract_and_convert_data(dump: str, tables: list[Table]) -> str:
    lines_out: list[str] = [
        "-- =============================================================================",
        "-- SaiFlower data load (PostgreSQL)",
        "-- =============================================================================",
        "",
        "BEGIN;",
        "",
        "-- Defer foreign-key checks until the transaction commits.",
        "SET CONSTRAINTS ALL DEFERRED;",
        "",
    ]

    inserts = find_insert_statements(dump)
    expected_counts = {table.name: 0 for table in tables}
    for tname, cols_raw, values in inserts:
        if tname not in expected_counts:
            raise ValueError(f"INSERT references unknown table: {tname}")
        expected_counts[tname] += count_value_rows(values)
        cols = re.findall(r"`([^`]+)`", cols_raw)
        col_list = ", ".join(quote_ident(c) for c in cols)
        vals = convert_mysql_values(values)
        stmt = f"INSERT INTO {quote_ident(tname)} ({col_list}) VALUES\n{vals};"
        lines_out.append(f"-- Data for {tname}")
        lines_out.append(stmt)
        lines_out.append("")

    lines_out.append("-- Sync identity sequences with loaded data")
    for tname in PRIMARY_KEYS:
        lines_out.append(
            f"SELECT setval(pg_get_serial_sequence('{tname}', 'id'), "
            f"COALESCE(MAX(id), 1), MAX(id) IS NOT NULL) FROM {quote_ident(tname)};"
        )

    lines_out.append("")
    lines_out.append("-- Abort the transaction if any converted row count is incomplete.")
    lines_out.append("DO $migration_verify$")
    lines_out.append("BEGIN")
    for tname, expected in expected_counts.items():
        lines_out.append(
            f"  IF (SELECT COUNT(*) FROM {quote_ident(tname)}) <> {expected} THEN "
            f"RAISE EXCEPTION 'Row-count mismatch for {tname}: expected {expected}'; END IF;"
        )
    lines_out.append("END")
    lines_out.append("$migration_verify$;")
    lines_out.append("")
    lines_out.append("COMMIT;")
    lines_out.append("")
    return "\n".join(lines_out)


# ---------------------------------------------------------------------------
# Prisma schema generation
# ---------------------------------------------------------------------------

def to_pascal(name: str) -> str:
    return "".join(p.capitalize() for p in name.split("_"))


def mysql_to_prisma_field(col: Column, is_pk: bool, has_identity: bool) -> tuple[str, list[str]]:
    """Return (field_line, extra_enums_needed)."""
    extras: list[str] = []
    t = col.mysql_type.strip().lower()
    attrs: list[str] = []

    if col.enum_name:
        enum_prisma = to_pascal(col.enum_name)
        prisma_type = enum_prisma
        extras.append(col.enum_name)
    elif t.startswith("tinyint(1)") or t.startswith("tinyint"):
        prisma_type = "Int"
        attrs.append("@db.SmallInt")
    elif t.startswith("smallint"):
        prisma_type = "Int"
        attrs.append("@db.SmallInt")
    elif t.startswith("int") or t.startswith("mediumint"):
        prisma_type = "Int"
    elif t.startswith("bigint"):
        prisma_type = "BigInt"
    elif t.startswith("decimal") or t.startswith("numeric"):
        prisma_type = "Decimal"
        mm = re.match(r"decimal\((\d+),(\d+)\)", t)
        if mm:
            attrs.append(f"@db.Decimal({mm.group(1)}, {mm.group(2)})")
    elif t.startswith("float"):
        prisma_type = "Float"
        attrs.append("@db.Real")
    elif t.startswith("double"):
        prisma_type = "Float"
        attrs.append("@db.DoublePrecision")
    elif t.startswith("datetime") or t.startswith("timestamp"):
        prisma_type = "DateTime"
        attrs.append("@db.Timestamptz(6)")
    elif t.startswith("date"):
        prisma_type = "DateTime"
        attrs.append("@db.Date")
    elif t.startswith("longtext") or t.startswith("mediumtext") or t.startswith("text"):
        prisma_type = "String"
        attrs.append("@db.Text")
    elif t.startswith("varchar"):
        prisma_type = "String"
        mm = re.match(r"varchar\((\d+)\)", t)
        if mm:
            attrs.append(f"@db.VarChar({mm.group(1)})")
    elif t.startswith("char"):
        prisma_type = "String"
        mm = re.match(r"char\((\d+)\)", t)
        if mm:
            attrs.append(f"@db.Char({mm.group(1)})")
    else:
        prisma_type = "String"
        attrs.append("@db.Text")

    optional = "" if col.not_null else "?"
    if is_pk:
        attrs.insert(0, "@id")
        if has_identity:
            attrs.insert(1, "@default(autoincrement())")

    # Defaults
    if col.default and not is_pk:
        d = col.default
        if re.match(r"current_timestamp\(\)", d, re.I):
            attrs.append("@default(now())")
        elif d.upper() != "NULL":
            if d.startswith("'") or d.startswith('"'):
                inner = d[1:-1]
                if col.enum_name:
                    # Prisma enum default must use the variant identifier, not DB value
                    if re.match(r"^[A-Z]", inner):
                        ident = inner
                    else:
                        ident = inner.upper()
                    attrs.append(f"@default({ident})")
                else:
                    escaped = inner.replace("\\", "\\\\").replace('"', '\\"')
                    attrs.append(f'@default("{escaped}")')
            else:
                # numeric
                if prisma_type == "Decimal":
                    attrs.append(f'@default({d})')
                elif prisma_type == "Int":
                    attrs.append(f"@default({int(float(d))})")
                else:
                    attrs.append(f"@default({d})")

    if col.on_update:
        attrs.append("@updatedAt")

    # camelCase field name mapped to snake column
    field_name = col.name
    # Keep snake_case field names matching DB for fidelity (@@map on model only)
    # Prisma convention: camelCase + @map
    camel = snake_to_camel(col.name)
    map_attr = f'@map("{col.name}")' if camel != col.name else ""
    if map_attr:
        attrs.append(map_attr)

    attr_str = (" " + " ".join(attrs)) if attrs else ""
    line = f"  {camel} {prisma_type}{optional}{attr_str}"
    return line, extras


def snake_to_camel(name: str) -> str:
    parts = name.split("_")
    return parts[0] + "".join(p.capitalize() for p in parts[1:])


def emit_prisma_schema(tables: list[Table]) -> str:
    enums_needed: dict[str, list[str]] = dict(ENUM_DEFS)
    for t in tables:
        for c in t.columns:
            if c.enum_name and c.enum_values:
                enums_needed[c.enum_name] = c.enum_values

    # Build relation maps
    # child_table -> list of (field, parent_model, parent_table, fk_cols, ref_cols, relation_name, on_delete)
    children: dict[str, list] = {}
    parents: dict[str, list] = {}  # parent_table -> list of (child_model, relation_name)

    def add_rel(child_t: str, fk_col: str, parent_t: str, ref_col: str, on_delete: Optional[str], name: str):
        children.setdefault(child_t, []).append({
            "fk": fk_col,
            "parent": parent_t,
            "ref": ref_col,
            "on_delete": on_delete,
            "name": name,
        })
        parents.setdefault(parent_t, []).append({
            "child": child_t,
            "name": name,
        })

    for tname, cname, col, rt, rc, on_delete in FOREIGN_KEYS:
        add_rel(tname, col, rt, rc, "Cascade" if on_delete == "CASCADE" else None, cname)

    for child_t, fk, parent_t, ref in SOFT_RELATIONS:
        if any(r["fk"] == fk and r["parent"] == parent_t for r in children.get(child_t, [])):
            continue
        add_rel(child_t, fk, parent_t, ref, None, f"{child_t}_{fk}_fkey")

    lines: list[str] = [
        "// =============================================================================",
        "// SaiFlower Prisma schema — legacy tables on PostgreSQL",
        "// Generated from: u977002836_Saiflower999 MySQL dump",
        "// Preserves: table names (@@map), integer IDs, relations, constraints",
        "// =============================================================================",
        "",
        "generator client {",
        '  provider = "prisma-client-js"',
        "}",
        "",
        "datasource db {",
        '  provider = "postgresql"',
        '  url      = env("DATABASE_URL")',
        "}",
        "",
    ]

    for ename, values in enums_needed.items():
        lines.append(f"enum {to_pascal(ename)} {{")
        for v in values:
            # Prisma enum values must be valid identifiers — quote if needed
            if re.match(r"^[A-Za-z_][A-Za-z0-9_]*$", v):
                # Capitalize for style but map — keep exact DB value via @map
                safe = v
                if safe[0].islower() or safe in ("pending", "approved", "percentage", "flat", "flower", "cake", "gift"):
                    # Use upper/pascal-ish identifier
                    ident = safe.upper() if safe.islower() else safe
                    # For mixed like Pending — keep as-is if valid
                    if re.match(r"^[A-Z]", safe):
                        ident = safe
                    else:
                        ident = safe.upper()
                    lines.append(f'  {ident} @map("{v}")')
                else:
                    lines.append(f"  {safe}")
            else:
                ident = re.sub(r"[^A-Za-z0-9_]", "_", v).upper()
                lines.append(f'  {ident} @map("{v}")')
        lines.append(f'  @@map("{ename}")')
        lines.append("}")
        lines.append("")

    unique_by_table: dict[str, list[tuple[str, list[str]]]] = {}
    for tname, uname, cols in UNIQUE_KEYS:
        unique_by_table.setdefault(tname, []).append((uname, cols))

    index_by_table: dict[str, list[tuple[str, list[str]]]] = {}
    for tname, iname, cols in INDEXES:
        index_by_table.setdefault(tname, []).append((iname, cols))

    for t in tables:
        model = to_pascal(t.name)
        lines.append(f"model {model} {{")
        pk_cols = set(PRIMARY_KEYS.get(t.name, ["id"]))
        has_id = t.name in AUTO_INCREMENT or t.name == "global_pricing"

        fk_cols = {r["fk"] for r in children.get(t.name, [])}

        for c in t.columns:
            is_pk = c.name in pk_cols
            field_line, _ = mysql_to_prisma_field(c, is_pk, has_id and is_pk and c.name == "id")
            lines.append(field_line)

        # Relation fields on child
        for r in children.get(t.name, []):
            parent_model = to_pascal(r["parent"])
            rel_field = snake_to_camel(r["parent"].rstrip("s") if False else r["parent"])
            # nicer names
            if r["fk"].endswith("_id"):
                rel_field = snake_to_camel(r["fk"][:-3])
            else:
                rel_field = snake_to_camel(r["parent"])
            # avoid collision with column names
            if any(snake_to_camel(c.name) == rel_field for c in t.columns):
                rel_field = rel_field + "Ref"
            on_del = f", onDelete: Cascade" if r["on_delete"] == "Cascade" else ""
            fk_camel = snake_to_camel(r["fk"])
            ref_camel = snake_to_camel(r["ref"])
            lines.append(
                f'  {rel_field} {parent_model} @relation("{r["name"]}", fields: [{fk_camel}], references: [{ref_camel}]{on_del})'
            )

        # Relation arrays on parent
        for r in parents.get(t.name, []):
            child_model = to_pascal(r["child"])
            arr_name = snake_to_camel(r["child"])
            lines.append(f'  {arr_name} {child_model}[] @relation("{r["name"]}")')

        # Unique
        for uname, cols in unique_by_table.get(t.name, []):
            camels = ", ".join(snake_to_camel(c) for c in cols)
            if len(cols) == 1:
                # already can use @unique on field — also emit @@unique for named constraint
                lines.append(f"  @@unique([{camels}], map: \"{uname}\")")
            else:
                lines.append(f"  @@unique([{camels}], map: \"{uname}\")")

        for iname, cols in index_by_table.get(t.name, []):
            camels = ", ".join(snake_to_camel(c) for c in cols)
            lines.append(f'  @@index([{camels}], map: "{iname}")')

        lines.append(f'  @@map("{t.name}")')
        lines.append("}")
        lines.append("")

    return "\n".join(lines)


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(
        description="Convert a SaiFlower MySQL/MariaDB dump to PostgreSQL SQL.",
    )
    parser.add_argument("dump", type=Path, help="Path to the source MySQL SQL dump")
    parser.add_argument(
        "--output-dir",
        type=Path,
        default=OUT_DIR / "migration-output",
        help="Directory for generated SQL (default: ignored migration-output folder)",
    )
    parser.add_argument(
        "--schema-file",
        type=Path,
        help="Write only PostgreSQL DDL to this file (no customer data output)",
    )
    return parser.parse_args()


def main() -> int:
    args = parse_args()
    mysql_dump = args.dump.resolve()
    output_dir = args.output_dir.resolve()

    if not mysql_dump.exists():
        print(f"Dump not found: {mysql_dump}", file=sys.stderr)
        return 1

    print(f"Reading {mysql_dump} ...")
    dump = mysql_dump.read_text(encoding="utf-8", errors="replace")

    tables = parse_tables(dump)
    table_names = {table.name for table in tables}
    print(f"Parsed {len(tables)} tables: {', '.join(t.name for t in tables)}")
    if "customer_addresses" not in table_names:
        print("Dump is missing required table: customer_addresses", file=sys.stderr)
        return 1

    schema_sql = emit_schema_sql(tables)
    schema_path = args.schema_file.resolve() if args.schema_file else output_dir / "01_schema_postgresql.sql"
    schema_path.parent.mkdir(parents=True, exist_ok=True)
    schema_path.write_text(schema_sql, encoding="utf-8")
    print(f"Wrote {schema_path}")

    if args.schema_file:
        print("Done. Schema-only output contains no customer data.")
        return 0

    output_dir.mkdir(parents=True, exist_ok=True)
    data_sql = extract_and_convert_data(dump, tables)
    data_path = output_dir / "02_data_postgresql.sql"
    data_path.write_text(data_sql, encoding="utf-8")
    print(f"Wrote {data_path} ({len(data_sql):,} chars)")

    full_path = output_dir / "saiflower_postgresql_full.sql"
    full_path.write_text(
        schema_sql.rstrip() + "\n\n" + data_sql + "\n",
        encoding="utf-8",
    )
    print(f"Wrote {full_path}")

    print("Done. Generated data files may contain private customer information; do not commit them.")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
