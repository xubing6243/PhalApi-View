<?php

/**
 *  Lite.php
 *  视图
 *  
 *  Created by SteveAK on 08/29/17
 *  Copyright (c) 2017 SteveAK. All rights reserved.
 *  Contact email(aer_c@qq.com) or qq(7579476)
 *  2018-08-13 @chenall
 *  1. 去除模板目录硬编码内容(原来固定为API_ROOT/$ITEM/src/view),但是不是所有人都是使用这种路径的.
 *  2. 修改调用罗逻辑,自动根据当前调用的API服务名称确定要调用的模板文件位置.
 *     比如调用Api.Site.Index服务,相应的模板文件为 View/Site/Index.html
 *  3. 模板文件不存在时不显示完整路径避免关键信息泄露.
 */
namespace SteveAK\View;

class Lite
{
	//模板赋值参数
	protected $param = array();
	private $view_root = '';
	private $action = '';

	public function __construct()
	{
		$request = \PhalApi\DI()->request;
		$server = $request->getServiceApi();
		$this->action = $request->getServiceAction();
		$root = $request->getNamespace();
		$root .= '\\Api\\' . $server;
		//通过反射获取当前API对应的视图文件位置
		$path = new \ReflectionClass($root);
		$root = dirname($path->getFileName());
		$this->view_root = $root . '/../View/' . $server . '/';
	}
	/**
	 * 渲染模板
	 * @param  array  $param 参数
	 */
	public function show($param = array())
	{
		$this->load($this->action, $param);
		exit();
	}
	/**
	 * 模板赋值
	 * @param  array  $param 参数 $K => $v
	 */
	public function assign($param = array())
	{
		foreach ($param as $k => $v) {
			$this->param[$k] = $v;
		}
		return true;
	}

	/**
	 * 装载模板
	 * @param string @name 模板文件名
	 * @param  array  $param 可选参数要传入的值
	 */
	public function load($name, $param = [])
	{
		$view = $this->view_root . $name . '.html';
        //合并参数
		$param = array_merge($this->param, $param);
        //将数组键名作为变量名，如果有冲突，则覆盖已有的变量
		extract($param, EXTR_OVERWRITE);
		ob_start();
		ob_implicit_flush(false);
		//检查文件是否存在
		if (file_exists($view)) {
			include_once($view);
		} else {
			echo "<!--${name}模板文件不存在-->";
		}
        //获取当前缓冲区内容 
		$content = ob_get_contents();
		return $content;
	}
}