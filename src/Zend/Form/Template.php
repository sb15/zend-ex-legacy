<?php

class Zend_Form_Template extends Zend_Form
{
    /**
     * Установить шаблон для формы
     *
     * @param string $template Имя файла с шаблоном без расширения
     */
    public function setTemplate($template, $options = null)
    {
		
		if (is_null($options)) {		
			$options = array();
		}
		
		if (is_array($options)) {
		    
		    $filter = new Zend_Filter_Word_CamelCaseToDash();
		    $template = $filter->filter($template);
		    
			$options['viewScript'] = 'forms/' . $template . '.phtml';
		}
		
        $this->setDecorators(array(
            array('viewScript', $options))
        );
    }

    /**
     * Добавление элемента в форму без декораторов
     *
     * @see Zend_Form::addElement()
     */
    public function addElement($element, $name = null, $options = null)
    {
        parent::addElement($element, $name, $options);

        if (isset($this->_elements[$name])) {
            $this->_elements[$name]->removeDecorator('Label');
            $this->_elements[$name]->removeDecorator('HtmlTag');
            $this->_elements[$name]->removeDecorator('DtDdWrapper');
            $this->_elements[$name]->removeDecorator('Description');
        }
		return $this;
    }

    /**
     * Создание элемента формы
     *
     * @see Zend_Form::createElement()
     */
    public function createElement($type, $name, $options = null)
    {
        $element = parent::createElement($type, $name, $options);
        //$element->removeDecorator('Label');
        //$element->removeDecorator('HtmlTag');
        //$element->removeDecorator('DtDdWrapper');
        //$element->removeDecorator('Description');
        return $element;
    }
}