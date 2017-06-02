<?php

namespace buddysoft\widget\utils;

use Yii;
use Carbon\Carbon;

class FileConvertor {

	private $imageDomain = '';
	private $uploadPath = '/upload/images/';

	/**
	 *
	 * 构造函数
	 * 
	 * @param string $imageDomain 能够指向 $uploadPath 的图片域名
	 * @param string $uploadPath 图片上传到应用的相对路径
	 *
	 */
	
	public function __construct($imageDomain, $uploadPath = null){
		if (substr($imageDomain, -1, 1) != '/') {
			$imageDomain .= '/';
		}
		$this->imageDomain = $imageDomain;

		if ($uploadPath != null) {
			// 目录最后如果没有反斜杠，帮它加上
			if (substr($uploadPath, -1, 1) != '/') {
				$uploadPath .= '/';	
			}

			$this->uploadPath = $uploadPath;			
		}

		// 检查目录是否存在，不存在时创建
		$path = $this->absoluteUploadPath($this->uploadPath);
		if (file_exists($path) == false) {
			if(mkdir($path, 0777, true) == false){
				Yii::error("create directory {$path} failed.");
			}
		}
	}

	/**
	 *
	 * 根据传入的文件名字，生成服务器上序列化的文件名
	 * 名字由子目录和最终文件名组成，形如： 2017/05/01-183000-asdf2334asdf.jpg
	 */
	
	private function serializedFilename($filename){
		$date = Carbon::now()->toDateTimeString();
		$year = substr($date, 0, 4);
		$month = substr($date, 4, 2);

		// 将 01 18:30:00 格式转化为 01-183000
		$name = substr($date, -11, 11);
		$name = str_replace(':', '', $name);
		$name = str_replace(' ', '-', $name);

		// 添加 uniqid 作为后缀
		$name .= "-" . uniqid();

		// 添加源文件后缀
		$pos = strrpos($filename, '.');
		if ($pos == false) {
			return null;
		}
		$suffix = substr($filename, $pos);
		$name .= $suffix;

		return "{$year}/{$month}/{$name}";
	}
	
	// 上传相对路径
	private function relativeUploadPath($filename){
		$serializedName = $this->serializedFilename($filename);
	    return [$this->uploadPath . $serializedName, $serializedName];
	}

	private function absoluteUploadPath($webPath, $checkCreateDirectory = true){
	    $path = Yii::getAlias('@webroot').$webPath;

	    if ($checkCreateDirectory == true) {
	    	$directory = dirname($path);
	    	if (file_exists($directory) == false) {
	    		mkdir($directory, 0777, true);
	    	}
	    }

	    return $path;
	}

	/**
	 *
	 * 将缓冲区内 base64 编码的图片数据，以文件形式保存在服务器上，并将编码的图片数据
	 * 用 <img> 标签替换。 通过这种替换，能降低广告数据表单条存储容量，提升加载速度。
	 *
	 */
	
	public function convertImage($content){
	    $matches = [];
	    $ret = preg_match_all("/\<img src=\"data:image\/.*?;base64,(.*?)\" data-filename=\"(.*?)\"/", $content, $matches);
	    if (0 != $ret) {
	        $i = 0;
	        for ($i=0; $i < count($matches[0]); $i++) { 
	            $imageFilename = $matches[2][$i];

	            list($webPath, $convertedName) = $this->relativeUploadPath($imageFilename);
	            $uploadPath = $this->absoluteUploadPath($webPath);

	            // 将base64图片数据解码，并保存到本地文件系统中
	            $file = fopen($uploadPath, "w");
	            $base64 = $matches[1][$i];
	            $decoded = base64_decode($base64);
	            fwrite($file, $decoded);
	            fclose($file);

	            // 存放图片的域名+文件名得到访问图片的地址
	            $webPath = $this->imageDomain . $convertedName;
	            $content = str_replace($matches[0][$i], "<img src=\"" . $webPath . "\"", $content);
	        }
	        return $content;
	    }
	}

	/**
	 *
	 * 处理图片上传，生成相对于图片域名的地址
	 *
	 */
	
	public function handleUploadedFile($uploadedFileInstance){
		list($uploadPath, $serializedName) = $this->relativeUploadPath($uploadedFileInstance->name);
		$absolutePath = $this->absoluteUploadPath($uploadPath);
		$uploadedFileInstance->saveAs($absolutePath);

		return $this->imageDomain . $serializedFilename;
	}
}