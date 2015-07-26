<?
require_once 'Zend/Log.php';

class Zend_Log_Translate extends Zend_Log
{	
	private $__translateFolder = null;  
	
	public function log($message, $priority, $extras = null)
	{
		parent::log($message, $priority, $extras);
		$this->modifyTranslationFiles($message);
	}
	
	public function setTranslateFolder($folder)
	{
		$this->__translateFolder = $folder;
	}
	
	public function modifyTranslationFiles($message)
	{
		if (!$this->__translateFolder) {
			return false;
		}
		
		$message = explode(": ", $message);
		$message = $message['1'];
				
		if (is_dir($this->__translateFolder)) {
			$iterator = new RecursiveIteratorIterator(
                new RecursiveRegexIterator(
                    new RecursiveDirectoryIterator($this->__translateFolder, RecursiveDirectoryIterator::KEY_AS_PATHNAME),
                    '/^(?!.*(\.svn|\.cvs)).*$/', RecursiveRegexIterator::MATCH
                )
            );
            
            foreach ($iterator as $directory => $info) {
                $file = $info->getFilename();
                $fileWithPath = $this->__translateFolder . '/' . $file;
                
                if ($info->isFile()) {
                	$content = include $fileWithPath;
                	if (!array_key_exists($message, $content)) {
                		$newContent = "<?php\nreturn array(\n";
                		$content[$message] = $message;
                		foreach ($content as $k => $v) {
                			if (!empty($k)) {
                				$newContent .= "    '" . str_replace("'", "\'", $k) ."' => '" . str_replace("'", "\'", $v) ."',\n";
                			}
                		} 
                		$newContent .= ");";
                		file_put_contents($fileWithPath, $newContent);
                	}
                }                 
            }
		} 
		
	}
	
	
}