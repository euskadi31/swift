<?php
/**
 * @package     Swift
 * @author      Axel ETCHEVERRY <axel@etcheverry.biz>
 * @copyright   Copyright (c) 2011 Axel ETCHEVERRY (http://www.axel-etcheverry.com)
 * Displays     <a href="http://creativecommons.org/licenses/MIT/deed.fr">MIT</a>
 * @license     http://creativecommons.org/licenses/MIT/deed.fr    MIT
 */

namespace Swift\Rest
{
    require_once __DIR__ . '/RequestInterface.php';
    require_once __DIR__ . '/Request.php';
    require_once __DIR__ . '/Response.php';

    use Swift\Exception;

    abstract class Controller
    {
        /**
         *
         * @var Swift\Rest\RequestInterface
         */
        protected $_request;

        /**
         *
         * @var Swift\Rest\Response
         */
        protected $_response;

        /**
         * @var array of existing class methods
         */
        protected $_classMethods;

        public function __construct(RequestInterface $request, Response $response)
        {
            $this->setRequest($request)
                ->setResponse($response);

            $this->init();
        }

        public function init()
        {

        }

        /**
         * Set request
         * @param Swift\Rest\RequestInterface $request
         * @return Swift\Rest\Controller
         */
        public function setRequest(RequestInterface $request)
        {
            $this->_request = $request;
            return $this;
        }

        /**
         * Get request
         * @return Swift\Rest\RequestInterface
         */
        public function getRequest()
        {
            return $this->_request;
        }


        /**
         * Set response
         * @param Swift\Rest\Response $response
         * @return Swift\Rest\Controller
         */
        public function setResponse(Response $response)
        {
            $this->_response = $response;
            return $this;
        }

        /**
         * Get response
         * @return Swift\Rest\Response
         */
        public function getResponse()
        {
            return $this->_response;
        }

        /**
         * Get all params
         * @return array
         */
        protected function _getAllParams()
        {
            return $this->getRequest()->getParams();
        }

        /**
         * get a param by name
         * @param string $name
         * @param mixed $default
         * @return mixed
         */
        protected function _getParam($name, $default = null)
        {
            $value = $this->getRequest()->getParam($name);

            if(!$value) {
                return $default;
            }

            return $value;
        }

        /**
         * Get allowed method
         * @return string
         */
        protected function _getAllowMethod()
        {
            $allow = array();
            
            if(in_array('get', $this->_classMethods)) {
                $allow[] = 'GET';
            }
            
            if(in_array('post', $this->_classMethods)) {
                $allow[] = 'POST';
            }
            
            if(in_array('put', $this->_classMethods)) {
                $allow[] = 'PUT';
            }
            
            if(in_array('delete', $this->_classMethods)) {
                $allow[] = 'DELETE';
            }
            
            return (string)implode(', ', $allow);
        }

        /**
         * Dispatch the requested action
         *
         * @param string $action Method name of action
         * @return void
         */
        public function dispatch($action)
        {
            $action = strtolower($action);

            $this->preDispatch();

            if (null === $this->_classMethods) {
                $this->_classMethods = get_class_methods($this);
            }

            $this->_response->setHeader('Allow', $this->_getAllowMethod(), true);

            if(in_array($action, $this->_classMethods)) {
                $this->_response->setData((array)$this->$action());
            } else {
                throw new Exception(sprintf('Action "%s" does not exist', $action), 404);
            }

            $this->postDispatch();
        }

    }
}