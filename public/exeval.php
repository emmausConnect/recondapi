<?php
$script = "";
if (isset($_POST['script'])) {
    $script = $_POST['script'];
}
$result = "";
if ($script != "") {
    $result = eval($script);
}
$retour = <<<EOT
<!DOCTYPE html>
<HTML>
<HEAD>
</HEAD>
<body>
EOT;
$retour .= '$cellValue = "=a"; $b = str_starts_with($cellValue,"="); return "|".$b."|";';
$retour .= <<<EOT
<form action="exeval.php" method="post">
<textarea id="script" name="script" rows="20" cols="100">
$script
</textarea>
<input type="submit" value="Submit">
</form>
<hr>
<textarea  rows="20" cols="100">
EOT;
echo $retour;
echo $result;
echo "</textarea>";
?>
</body>
</html>