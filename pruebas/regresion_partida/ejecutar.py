#!/usr/bin/env python3
from pathlib import Path
import subprocess,sys
base=Path(__file__).resolve().parent
for name in ['ensayo_compatible_base.php', 'ensayo_compatible_precedencias.php', 'ensayo_fallback.php']:
 p=subprocess.run(['php',str(base/name)],capture_output=True,text=True,timeout=30)
 print(name, 'PASS' if p.returncode==0 and not p.stderr else 'FAIL')
 if p.returncode or p.stderr:
  print(p.stdout,p.stderr);sys.exit(1)
print('EXITO_REGRESION_AISLADA; no equivale a suite heredada ni integración completa')
