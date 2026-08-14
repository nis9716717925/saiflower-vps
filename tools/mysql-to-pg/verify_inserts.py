#!/usr/bin/env python3
import argparse
import re
from pathlib import Path

parser = argparse.ArgumentParser(description="Compare source and converted INSERT statements.")
parser.add_argument("dump", type=Path)
parser.add_argument(
    "data",
    type=Path,
    nargs="?",
    default=Path(__file__).resolve().parent / "migration-output" / "02_data_postgresql.sql",
)
args = parser.parse_args()

dump = args.dump.read_text(encoding="utf-8", errors="replace")
data = args.data.read_text(encoding="utf-8")

starts = len(re.findall(r"^INSERT INTO", dump, re.M))
outs = len(re.findall(r'^INSERT INTO', data, re.M))
print("INSERT statements in dump:", starts)
print("INSERT statements in output:", outs)

tables_with_data = re.findall(r"-- Dumping data for table `([^`]+)`", dump)
print("Tables with data sections:", len(tables_with_data))
for t in tables_with_data:
    in_out = f'INSERT INTO "{t}"' in data or f'INSERT INTO "{t}"' in data
    # also check without needing exact
    found = re.search(rf'INSERT INTO "{re.escape(t)}"', data) is not None
    print(f"  {t}: {'OK' if found else 'MISSING'}")

# Naive regex risk: check if any INSERT body was truncated oddly
pattern = re.compile(r"INSERT\s+INTO\s+`([^`]+)`\s*\(([^)]+)\)\s*VALUES\s*(.*?);", re.S | re.I)
naive = list(pattern.finditer(dump))
print("Naive regex matches:", len(naive))
