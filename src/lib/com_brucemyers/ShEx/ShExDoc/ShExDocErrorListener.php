<?php
namespace com_brucemyers\ShEx\ShExDoc;

use Antlr\Antlr4\Runtime\Error\Exceptions\RecognitionException;
use Antlr\Antlr4\Runtime\Error\Listeners\BaseErrorListener;
use Antlr\Antlr4\Runtime\Recognizer;

final class ShExDocErrorListener extends BaseErrorListener
{
    protected $errors = [];

    public function syntaxError(
        Recognizer $recognizer,
        ?object $offendingSymbol,
        int $line,
        int $charPositionInLine,
        string $msg,
        ?RecognitionException $e
        ) : void {
            $this->errors[] = ['line' => $line, 'charpos' => $charPositionInLine, 'msg' => $msg];
    }

    /**
     * getErrors
     *
     * @return array('line'=> int, 'charpos' => int, 'msg' => string)
     */
    public function getErrors() : array {
        return $this->errors;
    }
}
