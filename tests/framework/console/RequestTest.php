<?php

use yii\console\Request;
use yiiunit\TestCase;

/**
 * @group console
 */
class RequestTest extends TestCase
{
    public function provider()
    {
        return [
            [
                'params' => [
                    'controller',
                ],
                'expected' => [
                    'route' => 'controller',
                    'params' => [
                    ],
                ],
            ],
            [
                'params' => [
                    'controller/route',
                    'param1',
                    '-12345',
                    '--option1',
                    '--option2=testValue',
                    '-alias1',
                    '-alias2=testValue',
                ],
                'expected' => [
                    'route' => 'controller/route',
                    'params' => [
                        'param1',
                        '-12345',
                        'option1' => '1',
                        'option2' => 'testValue',
                        '_aliases' => [
                            'alias1' => true,
                            'alias2' => 'testValue',
                        ],
                    ],
                ],
            ],
            [
                // Case: Special argument "End of Options" used
                'params' => [
                    'controller/route',
                    'param1',
                    '-12345',
                    '--option1',
                    '--option2=testValue',
                    '-alias1',
                    '-alias2=testValue',
                    '--', // Special argument "End of Options"
                    'param2',
                    '-54321',
                    '--option3',
                    '--', // Second `--` argument shouldn't be treated as special
                    '--option4=testValue',
                    '-alias3',
                    '-alias4=testValue',
                ],
                'expected' => [
                    'route' => 'controller/route',
                    'params' => [
                        'param1',
                        '-12345',
                        'option1' => '1',
                        'option2' => 'testValue',
                        '_aliases' => [
                            'alias1' => true,
                            'alias2' => 'testValue',
                        ],
                        'param2',
                        '-54321',
                        '--option3',
                        '--',
                        '--option4=testValue',
                        '-alias3',
                        '-alias4=testValue',
                    ],
                ],
            ],
            [
                // Case: Special argument "End of Options" placed before route
                'params' => [
                    '--', // Special argument "End of Options"
                    'controller/route',
                    'param1',
                    '-12345',
                    '--option1',
                    '--option2=testValue',
                    '-alias1',
                    '-alias2=testValue',
                ],
                'expected' => [
                    'route' => 'controller/route',
                    'params' => [
                        'param1',
                        '-12345',
                        '--option1',
                        '--option2=testValue',
                        '-alias1',
                        '-alias2=testValue',
                    ],
                ],
            ],
            [
                // PHP does not allow variable name, starting with digit.
                // InvalidParamException must be thrown during request resolving:
                'params' => [
                    'controller/route',
                    '--0=test',
                    '--1=testing',
                ],
                'expected' => [
                    'route' => 'controller/route',
                    'params' => [
                    ],
                ],
                'exception' => [
                    '\yii\base\InvalidParamException',
                    'Parameter "0" is not valid'
                ],
            ],
        ];
    }

    /**
     * @dataProvider provider
     */
    public function testResolve($params, $expected, $expectedException = null)
    {
        if (isset($expectedException)) {
            $this->setExpectedException($expectedException[0], $expectedException[1]);
        }

        $request = new Request();

        $request->setParams($params);
        list($route, $params) = $request->resolve();
        $this->assertEquals($expected['route'], $route);
        $this->assertEquals($expected['params'], $params);
    }
}
