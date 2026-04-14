<?php
echo "<h2>Archivos en /var/www/html</h2><pre>";
print_r(scandir('/var/www/html'));
echo "</pre>";

echo "<h2>Existe fitly.db?</h2>";
echo file_exists('/var/www/html/fitly.db') ? "SI" : "NO";
?>