<?php
/*
 * emyi
 *
 * @link http://github.com/douggr/emyi for the canonical source repository
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace EmyiTest\Http;

use Emyi\Http\Request;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0
 */
class RequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Request
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new Request();
    }

    /**
     * @covers Emyi\Http\Request::fromString
     */
    public function testFromString()
    {
        $string = "GET /foo HTTP/1.1\r\n\r\nSome Content";
        $request = Request::fromString($string);

        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('/foo', $request->getRequestUri());
        $this->assertEquals('1.1', $request->getVersion());
        $this->assertEquals('Some Content', $request->getContent());
    }

    /**
     * @covers Emyi\Http\Request::setBaseHref
     */
    public function testSetBaseHref()
    {
        $ret = $this->object->setBaseHref('/foo/bar');
        $this->assertInstanceOf('Emyi\Http\Request', $ret);
    }

    /**
     * @covers Emyi\Http\Request::setRequestUri
     */
    public function testSetRequestUri()
    {
        $ret = $this->object->setRequestUri('/foo/bar');
        $this->assertInstanceOf('Emyi\Http\Request', $ret);
    }

    /**
     * @covers Emyi\Http\Request::getMethod
     * @covers Emyi\Http\Request::setMethod
     */
    public function testSetMethod()
    {
        /// Request must set only valid methods
        $request = new Request();

        $this->setExpectedException('\InvalidArgumentException', 'Invalid HTTP method passed');
        $request->setMethod('invalid');

        /// Request must always forces uppecase method name
        $request = new Request();
        $request->setMethod('get');
        $this->assertEquals('GET', $request->getMethod());
    }

    /**
     * @covers Emyi\Http\Request::isOptions
     * @covers Emyi\Http\Request::isGet
     * @covers Emyi\Http\Request::isHead
     * @covers Emyi\Http\Request::isPost
     * @covers Emyi\Http\Request::isPut
     * @covers Emyi\Http\Request::isDelete
     * @covers Emyi\Http\Request::isTrace
     * @covers Emyi\Http\Request::isConnect
     * @covers Emyi\Http\Request::isPatch
     *
     * @dataProvider getMethods
     */
    public function testRequestMethodCheckWorksForAllMethods($methodName)
    {
        $request = new Request();
        $request->setMethod($methodName);

        foreach ($this->getMethods(false, $methodName) as $testMethodName => $testMethodValue) {
            $this->assertEquals($testMethodValue, $request->{'is' . $testMethodName}());
        }
    }

    /**
     * @covers Emyi\Http\Request::isXmlHttpRequest
     */
    public function testIsXmlHttpRequest()
    {
        $request = new Request();
        $this->assertFalse($request->isXmlHttpRequest());

        $request = new Request();
        $request->addHeader('X_REQUESTED_WITH', 'FooBazBar');
        $this->assertFalse($request->isXmlHttpRequest());

        $request = new Request();
        $request->addHeader('X_REQUESTED_WITH', 'XMLHttpRequest');
        $this->assertTrue($request->isXmlHttpRequest());
    }

    /**
     * @covers Emyi\Http\Request::getRequestLine
     * @covers Emyi\Http\Request::__toString
     * @todo   Implement test__toString().
     */
    public function test__toString()
    {
        $request = new Request();
        $request->setMethod('GET');
        $request->setRequestUri('/');
        $request->setContent('foo=bar&bar=baz');
        $this->assertEquals("GET / HTTP/1.1\r\n\r\nfoo=bar&bar=baz", (string) $request);
    }

    /**
     * PHPUNIT DATA PROVIDER
     *
     * @param $providerContext
     * @param null $trueMethod
     * @return array
     */
    public function getMethods($providerContext, $trueMethod = null)
    {
        $refClass = new \ReflectionClass(new Request());
        $return = [];

        foreach ($refClass->getConstants() as $cName => $cValue) {
            if (substr($cName, 0, 6) == 'METHOD') {
                if ($providerContext) {
                    $return[] = array($cValue);
                } else {
                    $return[strtolower($cValue)] = ($trueMethod == $cValue) ? true : false;
                }
            }
        }
        return $return;
    }
}
