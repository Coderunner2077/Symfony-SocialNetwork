<?php
// src/LEF/GroupBundle/Bitmask/PrivilegeBitmasks.php

namespace LEF\GroupBundle\Bitmask;

class PrivilegeBitmasks {    
    const BLOCKED =               0b0;
    const VIEW =                  0b1;
    const COMMENT =              0b10;
    const SUBSCRIBER =           0b11;
    const POST =                0b100;
    const MEMBER =              0b111; // 7
    const CREATE =             0b1000;
    const COLUMNIST =          0b1111; // 15
    const EDIT =              0b10000; // 16
    const EDITOR =            0b10111; // 23
    const DELETE =           0b100000; // 32
    const MODERATOR =        0b110111; // 55
    const BLOCK =           0b1000000; // 64
    const OPERATOR =        0b1111111; // 127
    const HIRE =           0b10000000; // 128
    const HEADHUNTER =     0b10000111; // 135
    //const SUBMANAGER =     0b11111111; // 255
    const FIRE =          0b100000000; // 256
    const HR_MANAGER =    0b110000111; // 391 
    //const MANAGER =       0b111111111; // 511
    const GRANT =        0b1000000000; // 512
    const GRANTER =      0b1000000111; // 519
    const MANAGER =      0b1111111111; // 1023
    const DICTATE =     0b10000000000; // 1024
    const ADMIN =       0b11111111111; // 2047
    
    public static function getConstant($masks) {
        return constant('self::' . $masks);
    }
}