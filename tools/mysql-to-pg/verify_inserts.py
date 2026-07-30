#!/usr/bin/env python3
import re
from pathlib import Path

dump = Path(r"c:\Users\Nishant Singh\Downloads\u977002836_Saiflower999 (2).sql").read_text(
    encoding="utf-8", errors="replace"
)
data = Path(r"C:\Users\Nishant Singh\Desktop\saiflower-vps\tools\mysql-to-pg\02_data_postgresql.sql").read_text(
    encoding="utf-8"
)

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
