<?php
declare(strict_types=1);

/**
 * Copyright 2024, Portal89 (https://portal89.com.br)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2024, Portal89 (https://portal89.com.br)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace Portal89\OracleDriver\Test\CodeFixture;

use Portal89\OracleDriver\TestSuite\Fixture\MethodTestFixture;

class CalcCodeFixture extends MethodTestFixture
{
//    public $type = 'package';

    public $name = 'CALC';

    public $create = [];

    public $drop = 'drop package calc';

    public function __construct()
    {
        $this->create[] =
                "create or replace package calc is

                		function sum(a number, b number) return number;
                		PROCEDURE twice(a number, b out number);

                end calc;";
            $this->create[] =
                "
                create or replace package body calc is

                	function sum(a number, b number) return number is
                    begin
                        return a+b;
                    end;

                	PROCEDURE twice(a number, b out number) is
                    begin
                        b := 2*a;
                    end;

                end calc;";

        parent::__construct();
    }
}
