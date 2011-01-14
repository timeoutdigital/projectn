<?php





class A
{
    const C = 1;

    public static function getC()
    {
        return self::C;
    }
}

class B extends A
{
    const C = 2;
}

$b = new B();
echo $b->getC();




