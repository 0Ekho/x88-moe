<?php
class GetRandom
{
    public function string ($rsl = 24, $char_pool = false)
    {
        if ($char_pool == false) {
            $char_pool = implode(array_merge(range(0,9), range('a', 'z'),range('A', 'Z')));
        }
        $cpl = strlen($char_pool) - 1;
        $r_string = '';
        for ($i = 0; $i < $rsl; $i++) {
            $r_string .= $char_pool[random_int(0, $cpl)];
        }
        return $r_string;
    }
    // I did not write this, unfortunately I can not remember where I found it a few years ago while working on some other website
    public function GUIDv4()
    {
        return implode('-', [
            bin2hex(openssl_random_pseudo_bytes(4)),
            bin2hex(openssl_random_pseudo_bytes(2)),
            bin2hex(chr((ord(openssl_random_pseudo_bytes(1)) & 0x0F) | 0x40)) . bin2hex(openssl_random_pseudo_bytes(1)),
            bin2hex(chr((ord(openssl_random_pseudo_bytes(1)) & 0x3F) | 0x80)) . bin2hex(openssl_random_pseudo_bytes(1)),
            bin2hex(openssl_random_pseudo_bytes(6))
        ]);
    }
}
?>