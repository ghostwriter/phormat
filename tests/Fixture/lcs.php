<?php

declare(strict_types=1);

function lcs1(array $X, array $Y, $m, $n)
{
    if ($m === 0 || $n === 0) {
        return 0;
    }

    if ($X[$m - 1] === $Y[$n - 1]) {
        return 1 + \lcs1($X, $Y, $m - 1, $n - 1);
    }

    $a = \lcs1($X, $Y, $m, $n - 1);

    $b = \lcs1($X, $Y, $m - 1, $n);

    return $a > $b ? $a : $b;
}

function lcs(array $X, array $Y, $m, $n)
{
    if ($m === 0 || $n === 0) {
        return 0;
    }

    if ($X[$m - 1] === $Y[$n - 1]) {
        return 1 + \lcs($X, $Y, $m - 1, $n - 1);
    }

    return \max(\lcs($X, $Y, $m, $n - 1), \lcs($X, $Y, $m - 1, $n));
}

$S1 = 'AGGTAB';
$S2 = 'GXTXAYB';
echo 'Length of LCS is ';
echo \lcs($S1, $S2, \mb_strlen($S1), \mb_strlen($S2));
