<?php

namespace Friday\Core;

use Phalcon\Annotations\Annotation;
use Phalcon\Mvc\Router\Annotations;

class Router extends Annotations
{
	public function parseControllerAnnotation($module, $namespace, $controller, Annotation $annotation)
	{
		$name = $annotation->getName();

		$isRoute = true;
		$methods = "";

		switch ($name)
		{
			case "RoutePrefix":

				$this->_routePrefix = $annotation->getArgument(0);

				$isRoute = false;

				break;

			case "Route":
				break;

			case "Get":
				$methods = "GET";
				break;

			case "Post":
				$methods = "POST";
				break;

			case "Put":
				$methods = "PUT";
				break;

			case "Delete":
				$methods = "DELETE";
				break;

			case "Options":
				$methods = "OPTIONS";
				break;
		}

		if ($isRoute)
		{
			$routePrefix = $this->_routePrefix;
			$paths = $annotation->getNamedArgument("paths");

			if (!is_array($paths))
				$paths = [];

			if (!empty($module))
				$paths["module"] = $module;

			if (!empty($namespace))
				$paths["namespace"] = $namespace;

			$paths["controller"] = $controller;

			$value = $annotation->getArgument(0);

			if ($value)
			{
				$uri = str_replace('//', '/', $routePrefix.$value);

				$route = $this->add($uri, $paths);

				if ($methods != "")
					$route->via($methods);
				else {
					$methods = $annotation->getNamedArgument("methods");
					if (is_array($methods) || is_string($methods))
						$route->via($methods);
				}

				$routeName = $annotation->getNamedArgument("name");
				if (is_string($routeName))
					$route->setName($routeName);
			}
		}
	}

	public function parseControllers ()
	{
		$cache = $this->getDI()->getShared('cache');

		$resources = $cache->get('FRIDAY_ROUTER_RESOURCES');

		if (!is_array($resources))
		{
			$registry = $this->getDI()->getShared('registry');

			/**
			 * @var $annotationsService \Phalcon\Annotations\Adapter\Memory
			 */
			$annotationsService = $this->getDI()->getShared("annotations");

			foreach ($registry->controllers as $controller)
			{
				$handlerAnnotations = $annotationsService->get($controller['class'].$this->_controllerSuffix);

				if (!is_object($handlerAnnotations))
					continue;

				$classAnnotations = $handlerAnnotations->getClassAnnotations();

				if (!is_object($classAnnotations))
					continue;

				if ($classAnnotations->has('RoutePrefix'))
				{
					$prefix = $classAnnotations->get('RoutePrefix')->getArgument(0);

					$this->addModuleResource($controller['module'], $controller['class'], $prefix);

					continue;
				}

				$this->addModuleResource($controller['module'], $controller['class']);
			}

			$cache->save('FRIDAY_ROUTER_RESOURCES', $this->getResources(), 7200);
		}
		else
		{
			foreach ($resources as $resource)
			{
				$this->addModuleResource($resource[2], $resource[1], $resource[0]);
			}
		}
	}

	public function handle ($uri = null)
	{
		$this->parseControllers();

		if (!$uri)
			$realUri = $this->getRewriteUri();
		else
			$realUri = $uri;

		$url = $this->getDI()->getShared('url');

		if ($url->getBaseUri() != '/')
		{
			$realUri = '/'.trim(str_replace($url->getBaseUri(), '', $realUri), '/');

			if ($realUri != '/')
				$realUri = $realUri.'/';
		}

		/**
		 * @var $annotationsService \Phalcon\Annotations\Adapter\Memory
		 */
		$annotationsService = $this->getDI()->getShared("annotations");

		$handlers = $this->_handlers;

		$controllerSuffix = $this->_controllerSuffix;

		if ($this->getDI()->has('profiler'))
			$benchmark = $this->getDI()->getShared('profiler')->start(__CLASS__.'::handle', [], 'Router ');

		foreach ($handlers as $scope)
		{
			if (!is_array($scope))
				continue;

			if (!empty($scope[0]) && strpos($realUri, $scope[0]) !== 0)
				continue;

			if (strpos($scope[1], "\\") !== false)
			{
				$class = getClassName($scope[1]);

				$controllerName = $class['name'];
				$namespaceName = $class['namespace'];
			}
			else
			{
				$controllerName = $scope[1];
				$namespaceName = $this->_defaultNamespace;
			}

			$this->_routePrefix = null;

			$sufixed = $controllerName . $controllerSuffix;

			if ($namespaceName !== null)
				$sufixed = $namespaceName . "\\" . $sufixed;

			$handlerAnnotations = $annotationsService->get($sufixed);

			if (!is_object($handlerAnnotations))
				continue;

			$classAnnotations = $handlerAnnotations->getClassAnnotations();

			if (is_object($classAnnotations))
			{
				$annotations = $classAnnotations->getAnnotations();

				if (is_array($annotations))
				{
					foreach ($annotations as $annotation)
						$this->parseControllerAnnotation($scope[2], $namespaceName, mb_strtolower($controllerName), $annotation);
				}
			}
		}

		parent::handle($realUri);

		if ($this->getDI()->has('profiler') && isset($benchmark))
			$this->getDI()->getShared('profiler')->stop($benchmark);
	}
}