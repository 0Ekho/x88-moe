<?php
class Log
{
    private static $handle;

    public function __construct($file)
    {
        static::$handle = fopen($file, "a+");
    }
    public function __destruct()
    {
        fclose(static::$handle);
    }
    public function log($level, $message)
    {
        $dt = new DateTime();
        $time = $dt->format("Y-m-d H:i:s.u");
        unset($dt);
        fwrite(static::$handle, "[$level] $time | $message".PHP_EOL);
    }
    public function varDump($var)
    {
        ob_start();
        var_dump($var);
        $info = ob_get_contents();
        ob_end_clean();
        $this->log("$VARDUMP", $info);
    }
}
?>