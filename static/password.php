<?php

//some characters are missing because they can be ambiguous)
function random_str($length, $keyspace = '23456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ!@#$%^&*()-=_[]{}\|;:,<.>/?')
{
    $str = '';
    $max = mb_strlen($keyspace, '8bit') - 1;

    for ($i = 0; $i < $length; ++$i)
        $str .= $keyspace[random_int(0, $max)];

    return $str;
}

if (strcmp($_GET['length'], "") === 0)
    return;

echo '<pre style="font-size: 18">';
echo random_str($_GET['length']);
?>
