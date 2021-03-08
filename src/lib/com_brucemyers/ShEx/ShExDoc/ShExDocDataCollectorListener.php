<?php
namespace com_brucemyers\ShEx\ShExDoc;

use Antlr\Antlr4\Runtime\ParserRuleContext;

class ShExDocDataCollectorListener extends ShExDocBaseListener
{
    protected $prefixes = [];
    protected $prefixedNames = [];

    /**
     * {@inheritdoc}
     *
     */
    public function enterEveryRule(ParserRuleContext $context) : void {
        //echo "\tEnter " . substr(get_class($context), strrpos(get_class($context), '\\') + 1);
    }

    /**
     * {@inheritdoc}
     *
     */
    public function exitEveryRule(ParserRuleContext $context) : void {
        //echo "\tExit " . substr(get_class($context), strrpos(get_class($context), '\\') + 1) . " {$context->getText()}";
    }

    /**
     * {@inheritdoc}
     *
     */
    public function exitPrefixDecl(Context\PrefixDeclContext $context) : void {
        $this->prefixes[$context->PNAME_NS()->getSymbol()->getText()] = $context->IRIREF()->getSymbol()->getText();
    }

    /**
     * {@inheritdoc}
     *
     */
    public function exitPrefixedName(Context\PrefixedNameContext $context) : void {
        $pname_ln = $context->PNAME_LN();
        if (! $pname_ln) return;
        $symbol = $pname_ln->getSymbol();
        $this->prefixedNames[] = ['name' => $symbol->getText(), 'line' =>$symbol->getLine(), 'charpos' => $symbol->getCharPositionInLine()];
    }

    /**
     * getPrefixes
     *
     * @return array('name' => 'IRIREF')
     */
    public function getPrefixes() : array {
        return $this->prefixes;
    }

    /**
     * getPrefixedNames
     *
     * @return array('name' => string, 'line' => int, 'charpos' => int)
     */
    public function getPrefixedNames() : array {
        return $this->prefixedNames;
    }
}