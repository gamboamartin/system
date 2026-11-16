# Regresión aislada Partida

Ejecutar `python3 pruebas/regresion_partida/ejecutar.py`. No BD, red ni datos reales. La fuente candidata se obtiene del archivo real del paquete, no de una copia de candidato. Fixtures originales/ensayos previos se preservan para comparar errores y comportamiento; las expectativas no se cambian.

System: 12 casos contrato numérico, 11 precedencias y 27 fallback. Administrador:8 casos equivalencia del método real extraído con modelo de consulta simulado; NO ORM real en este runner. Ensayos ORM real/ACL en fixture privado constan en evidencia del proyecto Facturación.

No sustituye suite histórica completa: ésta necesita configuración de BD privada/aislamiento porque contiene elimina_todo y altas. No ejecutarla contra BD real. No publicación ni tag mientras cobertura/revisión no estén aprobadas.
