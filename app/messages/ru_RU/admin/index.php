<?
$host=GetEnv("HTTP_HOST");
Header("Location: http://$host");
?>