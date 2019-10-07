<?php

namespace Drupal\spoinf\inc;

/**
 * 
 */
class ProcessMessage{
	
	private static $instance;
    private $messages;

    /**
     * Constructor clase ProcessError
     */
    private function __construct()
    {
    	// Inicializo errors
        $this->messages = array();
    }

    /**
     * obtiene una única instancia de la clase
     * @return [ProcessError] unica instancia de la clase!
     */
    public static function getInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Retorna si tiene mensajes a mostrar
     * @return boolean [description]
     */
    public function hasMesages(){

        return !empty( $this->messages );

    }
    /**
     * Imprime mensages
     */
    public function printMessages()
    {
        foreach( $this->messages as $key => $message ){
            
            // Se agrega el mensage a la salida de mensages de drupal
            drupal_set_message( 'Mensaje'.$message['msg'], $message['type'] );
        }
    }

    /**
     * Agrega mensages
     * @param [String] $msg El texto del mensaje
     */
    public function addMessage( $code, $msg )
    {
        $drupalErorType = '';

        switch ($code) {
            case '401':
                $drupalErorType = 'error';
                break;
            default:
                $drupalErorType = 'status';
                break;
        }

    	array_push( $this->messages, array( 'type' => $drupalErorType, 'msg' => $msg ) );
    }

    /**
     * Limpia el array de errores almacenados hasta el momento
     */
    public function resetError()
    {
    	$this->messages = array();
    }


}