<?php
class Utils
{
    public function sanitizeArray($url)
    {
        if (is_array($url)) {
            foreach ($url as $key => $value) {
                $url[$key] = sanitizeArray($value);
            }
            return $url;
        }
        else {
            $url = preg_replace('/[^a-zA-Z0-9_\.\-&=@\s]/', '', $url);
            return $url;
        }
    }
}
?>