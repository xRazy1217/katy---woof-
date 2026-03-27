<?php
/**
 * Katy & Woof - Schema Definition
 * Combina las definiciones de tablas para auditoría y sincronización.
 */
return array_merge(
    require __DIR__ . '/schema-tables-1.php',
    require __DIR__ . '/schema-tables-2.php'
);
