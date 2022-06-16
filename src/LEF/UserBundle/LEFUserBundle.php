<?php

namespace LEF\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class LEFUserBundle extends Bundle
{
    public function getParent() {
        return 'FOSUserBundle';
    }
}
