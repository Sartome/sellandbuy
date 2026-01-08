<?php
// helpers/ERDiagram.php

class ERDiagram {
    private $db;
    private $dbName;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->dbName = $this->db->query("SELECT DATABASE() AS db")->fetchColumn();
    }

    /**
     * Returns schema structure: tables => [columns => [...]], foreign_keys => [...]
     */
    public function getSchema(): array {
        $schema = ['tables' => [], 'fks' => []];

        // Tables
        $stmt = $this->db->prepare("SELECT TABLE_NAME, TABLE_TYPE FROM information_schema.tables WHERE TABLE_SCHEMA = ? ORDER BY TABLE_NAME");
        $stmt->execute([$this->dbName]);
        $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($tables as $t) {
            $table = $t['TABLE_NAME'];
            $schema['tables'][$table] = ['columns' => []];

            $colStmt = $this->db->prepare("SELECT COLUMN_NAME, COLUMN_KEY, COLUMN_TYPE, IS_NULLABLE FROM information_schema.columns WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? ORDER BY ORDINAL_POSITION");
            $colStmt->execute([$this->dbName, $table]);
            $cols = $colStmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($cols as $c) {
                $schema['tables'][$table]['columns'][] = $c;
            }
        }

        // Foreign keys
        $fkStmt = $this->db->prepare("SELECT TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM information_schema.key_column_usage WHERE TABLE_SCHEMA = ? AND REFERENCED_TABLE_NAME IS NOT NULL");
        $fkStmt->execute([$this->dbName]);
        $fks = $fkStmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($fks as $fk) {
            $schema['fks'][] = $fk;
        }

        return $schema;
    }

    /**
     * Generate a simple SVG ER diagram from the schema
     */
    public function generateSvg(array $options = []): string {
        $schema = $this->getSchema();
        $tables = array_keys($schema['tables']);
        $count = count($tables);

        // Layout options
        $cols = $options['cols'] ?? 3; // number of columns in grid
        $boxWidth = $options['boxWidth'] ?? 260;
        $hPadding = $options['hPadding'] ?? 40;
        $vPadding = $options['vPadding'] ?? 40;
        $rowHeightBase = 30; // header height
        $rowLineHeight = 18; // per column

        // Compute positions
        $positions = [];
        $maxX = $maxY = 0;
        foreach ($tables as $i => $t) {
            $col = $i % $cols;
            $row = intdiv($i, $cols);
            $colCount = count($schema['tables'][$t]['columns']);
            $boxHeight = $rowHeightBase + ($colCount * $rowLineHeight) + 10;

            $x = $col * ($boxWidth + $hPadding) + 20;
            $y = $row * ($boxHeight + $vPadding) + 20;
            $positions[$t] = ['x' => $x, 'y' => $y, 'w' => $boxWidth, 'h' => $boxHeight];

            $maxX = max($maxX, $x + $boxWidth + 20);
            $maxY = max($maxY, $y + $boxHeight + 20);
        }

        // Start SVG
        $svg = [];
        $svg[] = "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"" . ($maxX + 20) . "\" height=\"" . ($maxY + 20) . "\" viewBox=\"0 0 " . ($maxX + 20) . " " . ($maxY + 20) . "\">";
        $svg[] = "<style> .tbl { font-family: Arial, Helvetica, sans-serif; font-size:12px; } .tbl-title { font-weight:bold; font-size:13px; } .col { font-size:11px; } .pk { font-weight:bold; } .fk { font-style:italic; opacity:0.9 } .line { stroke:#444; stroke-width:1.5; marker-end:url(#arrow); } </style>";

        // Arrow marker
        $svg[] = "<defs><marker id=\"arrow\" markerWidth=\"10\" markerHeight=\"10\" refX=\"6\" refY=\"5\" orient=\"auto\"><path d=\"M0,0 L10,5 L0,10 z\" fill=\"#444\"/></marker></defs>";

        // Draw boxes
        foreach ($tables as $t) {
            $p = $positions[$t];
            $x = $p['x']; $y = $p['y']; $w = $p['w']; $h = $p['h'];

            // Background rect
            $svg[] = "<g class=\"tbl\">";
            $svg[] = "<rect x=\"$x\" y=\"$y\" width=\"$w\" height=\"$h\" rx=\"6\" ry=\"6\" fill=\"#f8f9fa\" stroke=\"#ccc\"/>";
            // Title background
            $svg[] = "<rect x=\"$x\" y=\"$y\" width=\"$w\" height=\"28\" fill=\"#2d6cdf\" rx=\"6\" ry=\"6\"/>";
            $svg[] = "<text x=\"" . ($x + 8) . "\" y=\"" . ($y + 18) . "\" fill=\"#fff\" class=\"tbl-title\">" . htmlspecialchars($t) . "</text>";

            // Columns
            $cx = $x + 8;
            $cy = $y + 28 + 14;
            foreach ($schema['tables'][$t]['columns'] as $col) {
                $colName = htmlspecialchars($col['COLUMN_NAME']);
                $colClass = '';
                if ($col['COLUMN_KEY'] === 'PRI') {
                    $colClass = 'pk';
                } elseif ($this->isForeignKey($t, $col['COLUMN_NAME'])) {
                    $colClass = 'fk';
                }
                $svg[] = "<text x=\"$cx\" y=\"$cy\" class=\"col $colClass\">" . $colName . "</text>";
                $cy += $rowLineHeight;
            }

            $svg[] = "</g>";
        }

        // Draw FK lines
        foreach ($schema['fks'] as $fk) {
            $from = $fk['TABLE_NAME'];
            $to = $fk['REFERENCED_TABLE_NAME'];
            if (!isset($positions[$from]) || !isset($positions[$to])) continue;
            $p1 = $positions[$from];
            $p2 = $positions[$to];

            $x1 = $p1['x'] + $p1['w'];
            $y1 = $p1['y'] + ($p1['h']/2);
            $x2 = $p2['x'];
            $y2 = $p2['y'] + ($p2['h']/2);

            // Simple polyline with mid-point
            $mx = ($x1 + $x2) / 2;
            $svg[] = "<path d=\"M$x1 $y1 C $mx $y1, $mx $y2, $x2 $y2\" class=\"line\" fill=\"none\"/>";
        }

        $svg[] = "</svg>";
        return implode("\n", $svg);
    }

    private function isForeignKey(string $table, string $column): bool {
        $stmt = $this->db->prepare("SELECT 1 FROM information_schema.key_column_usage WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL LIMIT 1");
        $stmt->execute([$this->dbName, $table, $column]);
        return (bool)$stmt->fetchColumn();
    }
}
?>