<?php
function findFiles(string $directory) {
    $returnVar = [];
    foreach(scandir($directory) as $file) {
        if($file[0] == '.') continue;
        $fullFile = $directory.'/'.$file;
        if(is_dir($fullFile)) {
            $returnVar = array_merge($returnVar, findFiles($fullFile));
        } elseif(preg_match('@\.zep@', $fullFile)) {
            $returnVar[] = realpath($fullFile);
        }
    }
    return $returnVar;
}
$allFiles = findFiles(__DIR__.'/../bullhorn');
foreach($allFiles as $allFile) {
    echo 'Writing: '.$allFile."\n";
    if(preg_match('@^\s*namespace (?P<namespace>[A-Za-z\\\\]+);.*?(class|interface) (?P<className>[A-Za-z]+)\s@s', file_get_contents($allFile), $matches)) {
        $rootDir = __DIR__.'/../ide/'.str_replace('\\', '/', $matches['namespace']).'/';
        if(!is_dir($rootDir)) {
            mkdir($rootDir, 0777, true);
        }
        $fileName = $rootDir.$matches['className'].'.php';
        exec(__DIR__.'/../../../vendor/bin/zephir-ide-helper -f '.escapeshellarg($fileName).' '.escapeshellarg($allFile));
    } else {
        echo 'Invalid File: '.$allFile."\n";
    }
}
