<?php

/*
 * Generated from ShExDoc.g4 by ANTLR 4.8
 */

namespace com_brucemyers\ShEx\ShExDoc;
use Antlr\Antlr4\Runtime\Tree\ParseTreeListener;

/**
 * This interface defines a complete listener for a parse tree produced by
 * {@see ShExDocParser}.
 */
interface ShExDocListener extends ParseTreeListener {
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::shExDoc()}.
	 * @param $context The parse tree.
	 */
	public function enterShExDoc(Context\ShExDocContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::shExDoc()}.
	 * @param $context The parse tree.
	 */
	public function exitShExDoc(Context\ShExDocContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::directive()}.
	 * @param $context The parse tree.
	 */
	public function enterDirective(Context\DirectiveContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::directive()}.
	 * @param $context The parse tree.
	 */
	public function exitDirective(Context\DirectiveContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::baseDecl()}.
	 * @param $context The parse tree.
	 */
	public function enterBaseDecl(Context\BaseDeclContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::baseDecl()}.
	 * @param $context The parse tree.
	 */
	public function exitBaseDecl(Context\BaseDeclContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::prefixDecl()}.
	 * @param $context The parse tree.
	 */
	public function enterPrefixDecl(Context\PrefixDeclContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::prefixDecl()}.
	 * @param $context The parse tree.
	 */
	public function exitPrefixDecl(Context\PrefixDeclContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::importDecl()}.
	 * @param $context The parse tree.
	 */
	public function enterImportDecl(Context\ImportDeclContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::importDecl()}.
	 * @param $context The parse tree.
	 */
	public function exitImportDecl(Context\ImportDeclContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::notStartAction()}.
	 * @param $context The parse tree.
	 */
	public function enterNotStartAction(Context\NotStartActionContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::notStartAction()}.
	 * @param $context The parse tree.
	 */
	public function exitNotStartAction(Context\NotStartActionContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::start()}.
	 * @param $context The parse tree.
	 */
	public function enterStart(Context\StartContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::start()}.
	 * @param $context The parse tree.
	 */
	public function exitStart(Context\StartContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::startActions()}.
	 * @param $context The parse tree.
	 */
	public function enterStartActions(Context\StartActionsContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::startActions()}.
	 * @param $context The parse tree.
	 */
	public function exitStartActions(Context\StartActionsContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::statement()}.
	 * @param $context The parse tree.
	 */
	public function enterStatement(Context\StatementContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::statement()}.
	 * @param $context The parse tree.
	 */
	public function exitStatement(Context\StatementContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::shapeExprDecl()}.
	 * @param $context The parse tree.
	 */
	public function enterShapeExprDecl(Context\ShapeExprDeclContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::shapeExprDecl()}.
	 * @param $context The parse tree.
	 */
	public function exitShapeExprDecl(Context\ShapeExprDeclContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::shapeExpression()}.
	 * @param $context The parse tree.
	 */
	public function enterShapeExpression(Context\ShapeExpressionContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::shapeExpression()}.
	 * @param $context The parse tree.
	 */
	public function exitShapeExpression(Context\ShapeExpressionContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::inlineShapeExpression()}.
	 * @param $context The parse tree.
	 */
	public function enterInlineShapeExpression(Context\InlineShapeExpressionContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::inlineShapeExpression()}.
	 * @param $context The parse tree.
	 */
	public function exitInlineShapeExpression(Context\InlineShapeExpressionContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::shapeOr()}.
	 * @param $context The parse tree.
	 */
	public function enterShapeOr(Context\ShapeOrContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::shapeOr()}.
	 * @param $context The parse tree.
	 */
	public function exitShapeOr(Context\ShapeOrContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::inlineShapeOr()}.
	 * @param $context The parse tree.
	 */
	public function enterInlineShapeOr(Context\InlineShapeOrContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::inlineShapeOr()}.
	 * @param $context The parse tree.
	 */
	public function exitInlineShapeOr(Context\InlineShapeOrContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::shapeAnd()}.
	 * @param $context The parse tree.
	 */
	public function enterShapeAnd(Context\ShapeAndContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::shapeAnd()}.
	 * @param $context The parse tree.
	 */
	public function exitShapeAnd(Context\ShapeAndContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::inlineShapeAnd()}.
	 * @param $context The parse tree.
	 */
	public function enterInlineShapeAnd(Context\InlineShapeAndContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::inlineShapeAnd()}.
	 * @param $context The parse tree.
	 */
	public function exitInlineShapeAnd(Context\InlineShapeAndContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::shapeNot()}.
	 * @param $context The parse tree.
	 */
	public function enterShapeNot(Context\ShapeNotContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::shapeNot()}.
	 * @param $context The parse tree.
	 */
	public function exitShapeNot(Context\ShapeNotContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::inlineShapeNot()}.
	 * @param $context The parse tree.
	 */
	public function enterInlineShapeNot(Context\InlineShapeNotContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::inlineShapeNot()}.
	 * @param $context The parse tree.
	 */
	public function exitInlineShapeNot(Context\InlineShapeNotContext $context) : void;
	/**
	 * Enter a parse tree produced by the `shapeAtomNonLitNodeConstraint`
	 * labeled alternative in {@see ShExDocParser::shapeAtom()}.
	 * @param $context The parse tree.
	 */
	public function enterShapeAtomNonLitNodeConstraint(Context\ShapeAtomNonLitNodeConstraintContext $context) : void;
	/**
	 * Exit a parse tree produced by the `shapeAtomNonLitNodeConstraint` labeled alternative
	 * in {@see ShExDocParser::shapeAtom()}.
	 * @param $context The parse tree.
	 */
	public function exitShapeAtomNonLitNodeConstraint(Context\ShapeAtomNonLitNodeConstraintContext $context) : void;
	/**
	 * Enter a parse tree produced by the `shapeAtomLitNodeConstraint`
	 * labeled alternative in {@see ShExDocParser::shapeAtom()}.
	 * @param $context The parse tree.
	 */
	public function enterShapeAtomLitNodeConstraint(Context\ShapeAtomLitNodeConstraintContext $context) : void;
	/**
	 * Exit a parse tree produced by the `shapeAtomLitNodeConstraint` labeled alternative
	 * in {@see ShExDocParser::shapeAtom()}.
	 * @param $context The parse tree.
	 */
	public function exitShapeAtomLitNodeConstraint(Context\ShapeAtomLitNodeConstraintContext $context) : void;
	/**
	 * Enter a parse tree produced by the `shapeAtomShapeOrRef`
	 * labeled alternative in {@see ShExDocParser::shapeAtom()}.
	 * @param $context The parse tree.
	 */
	public function enterShapeAtomShapeOrRef(Context\ShapeAtomShapeOrRefContext $context) : void;
	/**
	 * Exit a parse tree produced by the `shapeAtomShapeOrRef` labeled alternative
	 * in {@see ShExDocParser::shapeAtom()}.
	 * @param $context The parse tree.
	 */
	public function exitShapeAtomShapeOrRef(Context\ShapeAtomShapeOrRefContext $context) : void;
	/**
	 * Enter a parse tree produced by the `shapeAtomShapeExpression`
	 * labeled alternative in {@see ShExDocParser::shapeAtom()}.
	 * @param $context The parse tree.
	 */
	public function enterShapeAtomShapeExpression(Context\ShapeAtomShapeExpressionContext $context) : void;
	/**
	 * Exit a parse tree produced by the `shapeAtomShapeExpression` labeled alternative
	 * in {@see ShExDocParser::shapeAtom()}.
	 * @param $context The parse tree.
	 */
	public function exitShapeAtomShapeExpression(Context\ShapeAtomShapeExpressionContext $context) : void;
	/**
	 * Enter a parse tree produced by the `shapeAtomAny`
	 * labeled alternative in {@see ShExDocParser::shapeAtom()}.
	 * @param $context The parse tree.
	 */
	public function enterShapeAtomAny(Context\ShapeAtomAnyContext $context) : void;
	/**
	 * Exit a parse tree produced by the `shapeAtomAny` labeled alternative
	 * in {@see ShExDocParser::shapeAtom()}.
	 * @param $context The parse tree.
	 */
	public function exitShapeAtomAny(Context\ShapeAtomAnyContext $context) : void;
	/**
	 * Enter a parse tree produced by the `inlineShapeAtomNonLitNodeConstraint`
	 * labeled alternative in {@see ShExDocParser::inlineShapeAtom()}.
	 * @param $context The parse tree.
	 */
	public function enterInlineShapeAtomNonLitNodeConstraint(Context\InlineShapeAtomNonLitNodeConstraintContext $context) : void;
	/**
	 * Exit a parse tree produced by the `inlineShapeAtomNonLitNodeConstraint` labeled alternative
	 * in {@see ShExDocParser::inlineShapeAtom()}.
	 * @param $context The parse tree.
	 */
	public function exitInlineShapeAtomNonLitNodeConstraint(Context\InlineShapeAtomNonLitNodeConstraintContext $context) : void;
	/**
	 * Enter a parse tree produced by the `inlineShapeAtomLitNodeConstraint`
	 * labeled alternative in {@see ShExDocParser::inlineShapeAtom()}.
	 * @param $context The parse tree.
	 */
	public function enterInlineShapeAtomLitNodeConstraint(Context\InlineShapeAtomLitNodeConstraintContext $context) : void;
	/**
	 * Exit a parse tree produced by the `inlineShapeAtomLitNodeConstraint` labeled alternative
	 * in {@see ShExDocParser::inlineShapeAtom()}.
	 * @param $context The parse tree.
	 */
	public function exitInlineShapeAtomLitNodeConstraint(Context\InlineShapeAtomLitNodeConstraintContext $context) : void;
	/**
	 * Enter a parse tree produced by the `inlineShapeAtomShapeOrRef`
	 * labeled alternative in {@see ShExDocParser::inlineShapeAtom()}.
	 * @param $context The parse tree.
	 */
	public function enterInlineShapeAtomShapeOrRef(Context\InlineShapeAtomShapeOrRefContext $context) : void;
	/**
	 * Exit a parse tree produced by the `inlineShapeAtomShapeOrRef` labeled alternative
	 * in {@see ShExDocParser::inlineShapeAtom()}.
	 * @param $context The parse tree.
	 */
	public function exitInlineShapeAtomShapeOrRef(Context\InlineShapeAtomShapeOrRefContext $context) : void;
	/**
	 * Enter a parse tree produced by the `inlineShapeAtomShapeExpression`
	 * labeled alternative in {@see ShExDocParser::inlineShapeAtom()}.
	 * @param $context The parse tree.
	 */
	public function enterInlineShapeAtomShapeExpression(Context\InlineShapeAtomShapeExpressionContext $context) : void;
	/**
	 * Exit a parse tree produced by the `inlineShapeAtomShapeExpression` labeled alternative
	 * in {@see ShExDocParser::inlineShapeAtom()}.
	 * @param $context The parse tree.
	 */
	public function exitInlineShapeAtomShapeExpression(Context\InlineShapeAtomShapeExpressionContext $context) : void;
	/**
	 * Enter a parse tree produced by the `inlineShapeAtomAny`
	 * labeled alternative in {@see ShExDocParser::inlineShapeAtom()}.
	 * @param $context The parse tree.
	 */
	public function enterInlineShapeAtomAny(Context\InlineShapeAtomAnyContext $context) : void;
	/**
	 * Exit a parse tree produced by the `inlineShapeAtomAny` labeled alternative
	 * in {@see ShExDocParser::inlineShapeAtom()}.
	 * @param $context The parse tree.
	 */
	public function exitInlineShapeAtomAny(Context\InlineShapeAtomAnyContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::shapeOrRef()}.
	 * @param $context The parse tree.
	 */
	public function enterShapeOrRef(Context\ShapeOrRefContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::shapeOrRef()}.
	 * @param $context The parse tree.
	 */
	public function exitShapeOrRef(Context\ShapeOrRefContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::inlineShapeOrRef()}.
	 * @param $context The parse tree.
	 */
	public function enterInlineShapeOrRef(Context\InlineShapeOrRefContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::inlineShapeOrRef()}.
	 * @param $context The parse tree.
	 */
	public function exitInlineShapeOrRef(Context\InlineShapeOrRefContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::shapeRef()}.
	 * @param $context The parse tree.
	 */
	public function enterShapeRef(Context\ShapeRefContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::shapeRef()}.
	 * @param $context The parse tree.
	 */
	public function exitShapeRef(Context\ShapeRefContext $context) : void;
	/**
	 * Enter a parse tree produced by the `nodeConstraintLiteral`
	 * labeled alternative in {@see ShExDocParser::inlineLitNodeConstraint()}.
	 * @param $context The parse tree.
	 */
	public function enterNodeConstraintLiteral(Context\NodeConstraintLiteralContext $context) : void;
	/**
	 * Exit a parse tree produced by the `nodeConstraintLiteral` labeled alternative
	 * in {@see ShExDocParser::inlineLitNodeConstraint()}.
	 * @param $context The parse tree.
	 */
	public function exitNodeConstraintLiteral(Context\NodeConstraintLiteralContext $context) : void;
	/**
	 * Enter a parse tree produced by the `nodeConstraintNonLiteral`
	 * labeled alternative in {@see ShExDocParser::inlineLitNodeConstraint()}.
	 * @param $context The parse tree.
	 */
	public function enterNodeConstraintNonLiteral(Context\NodeConstraintNonLiteralContext $context) : void;
	/**
	 * Exit a parse tree produced by the `nodeConstraintNonLiteral` labeled alternative
	 * in {@see ShExDocParser::inlineLitNodeConstraint()}.
	 * @param $context The parse tree.
	 */
	public function exitNodeConstraintNonLiteral(Context\NodeConstraintNonLiteralContext $context) : void;
	/**
	 * Enter a parse tree produced by the `nodeConstraintDatatype`
	 * labeled alternative in {@see ShExDocParser::inlineLitNodeConstraint()}.
	 * @param $context The parse tree.
	 */
	public function enterNodeConstraintDatatype(Context\NodeConstraintDatatypeContext $context) : void;
	/**
	 * Exit a parse tree produced by the `nodeConstraintDatatype` labeled alternative
	 * in {@see ShExDocParser::inlineLitNodeConstraint()}.
	 * @param $context The parse tree.
	 */
	public function exitNodeConstraintDatatype(Context\NodeConstraintDatatypeContext $context) : void;
	/**
	 * Enter a parse tree produced by the `nodeConstraintValueSet`
	 * labeled alternative in {@see ShExDocParser::inlineLitNodeConstraint()}.
	 * @param $context The parse tree.
	 */
	public function enterNodeConstraintValueSet(Context\NodeConstraintValueSetContext $context) : void;
	/**
	 * Exit a parse tree produced by the `nodeConstraintValueSet` labeled alternative
	 * in {@see ShExDocParser::inlineLitNodeConstraint()}.
	 * @param $context The parse tree.
	 */
	public function exitNodeConstraintValueSet(Context\NodeConstraintValueSetContext $context) : void;
	/**
	 * Enter a parse tree produced by the `nodeConstraintNumericFacet`
	 * labeled alternative in {@see ShExDocParser::inlineLitNodeConstraint()}.
	 * @param $context The parse tree.
	 */
	public function enterNodeConstraintNumericFacet(Context\NodeConstraintNumericFacetContext $context) : void;
	/**
	 * Exit a parse tree produced by the `nodeConstraintNumericFacet` labeled alternative
	 * in {@see ShExDocParser::inlineLitNodeConstraint()}.
	 * @param $context The parse tree.
	 */
	public function exitNodeConstraintNumericFacet(Context\NodeConstraintNumericFacetContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::litNodeConstraint()}.
	 * @param $context The parse tree.
	 */
	public function enterLitNodeConstraint(Context\LitNodeConstraintContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::litNodeConstraint()}.
	 * @param $context The parse tree.
	 */
	public function exitLitNodeConstraint(Context\LitNodeConstraintContext $context) : void;
	/**
	 * Enter a parse tree produced by the `litNodeConstraintLiteral`
	 * labeled alternative in {@see ShExDocParser::inlineNonLitNodeConstraint()}.
	 * @param $context The parse tree.
	 */
	public function enterLitNodeConstraintLiteral(Context\LitNodeConstraintLiteralContext $context) : void;
	/**
	 * Exit a parse tree produced by the `litNodeConstraintLiteral` labeled alternative
	 * in {@see ShExDocParser::inlineNonLitNodeConstraint()}.
	 * @param $context The parse tree.
	 */
	public function exitLitNodeConstraintLiteral(Context\LitNodeConstraintLiteralContext $context) : void;
	/**
	 * Enter a parse tree produced by the `litNodeConstraintStringFacet`
	 * labeled alternative in {@see ShExDocParser::inlineNonLitNodeConstraint()}.
	 * @param $context The parse tree.
	 */
	public function enterLitNodeConstraintStringFacet(Context\LitNodeConstraintStringFacetContext $context) : void;
	/**
	 * Exit a parse tree produced by the `litNodeConstraintStringFacet` labeled alternative
	 * in {@see ShExDocParser::inlineNonLitNodeConstraint()}.
	 * @param $context The parse tree.
	 */
	public function exitLitNodeConstraintStringFacet(Context\LitNodeConstraintStringFacetContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::nonLitNodeConstraint()}.
	 * @param $context The parse tree.
	 */
	public function enterNonLitNodeConstraint(Context\NonLitNodeConstraintContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::nonLitNodeConstraint()}.
	 * @param $context The parse tree.
	 */
	public function exitNonLitNodeConstraint(Context\NonLitNodeConstraintContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::nonLiteralKind()}.
	 * @param $context The parse tree.
	 */
	public function enterNonLiteralKind(Context\NonLiteralKindContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::nonLiteralKind()}.
	 * @param $context The parse tree.
	 */
	public function exitNonLiteralKind(Context\NonLiteralKindContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::xsFacet()}.
	 * @param $context The parse tree.
	 */
	public function enterXsFacet(Context\XsFacetContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::xsFacet()}.
	 * @param $context The parse tree.
	 */
	public function exitXsFacet(Context\XsFacetContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::stringFacet()}.
	 * @param $context The parse tree.
	 */
	public function enterStringFacet(Context\StringFacetContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::stringFacet()}.
	 * @param $context The parse tree.
	 */
	public function exitStringFacet(Context\StringFacetContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::stringLength()}.
	 * @param $context The parse tree.
	 */
	public function enterStringLength(Context\StringLengthContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::stringLength()}.
	 * @param $context The parse tree.
	 */
	public function exitStringLength(Context\StringLengthContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::numericFacet()}.
	 * @param $context The parse tree.
	 */
	public function enterNumericFacet(Context\NumericFacetContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::numericFacet()}.
	 * @param $context The parse tree.
	 */
	public function exitNumericFacet(Context\NumericFacetContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::numericRange()}.
	 * @param $context The parse tree.
	 */
	public function enterNumericRange(Context\NumericRangeContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::numericRange()}.
	 * @param $context The parse tree.
	 */
	public function exitNumericRange(Context\NumericRangeContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::numericLength()}.
	 * @param $context The parse tree.
	 */
	public function enterNumericLength(Context\NumericLengthContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::numericLength()}.
	 * @param $context The parse tree.
	 */
	public function exitNumericLength(Context\NumericLengthContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::rawNumeric()}.
	 * @param $context The parse tree.
	 */
	public function enterRawNumeric(Context\RawNumericContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::rawNumeric()}.
	 * @param $context The parse tree.
	 */
	public function exitRawNumeric(Context\RawNumericContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::shapeDefinition()}.
	 * @param $context The parse tree.
	 */
	public function enterShapeDefinition(Context\ShapeDefinitionContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::shapeDefinition()}.
	 * @param $context The parse tree.
	 */
	public function exitShapeDefinition(Context\ShapeDefinitionContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::inlineShapeDefinition()}.
	 * @param $context The parse tree.
	 */
	public function enterInlineShapeDefinition(Context\InlineShapeDefinitionContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::inlineShapeDefinition()}.
	 * @param $context The parse tree.
	 */
	public function exitInlineShapeDefinition(Context\InlineShapeDefinitionContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::qualifier()}.
	 * @param $context The parse tree.
	 */
	public function enterQualifier(Context\QualifierContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::qualifier()}.
	 * @param $context The parse tree.
	 */
	public function exitQualifier(Context\QualifierContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::extraPropertySet()}.
	 * @param $context The parse tree.
	 */
	public function enterExtraPropertySet(Context\ExtraPropertySetContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::extraPropertySet()}.
	 * @param $context The parse tree.
	 */
	public function exitExtraPropertySet(Context\ExtraPropertySetContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::tripleExpression()}.
	 * @param $context The parse tree.
	 */
	public function enterTripleExpression(Context\TripleExpressionContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::tripleExpression()}.
	 * @param $context The parse tree.
	 */
	public function exitTripleExpression(Context\TripleExpressionContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::oneOfTripleExpr()}.
	 * @param $context The parse tree.
	 */
	public function enterOneOfTripleExpr(Context\OneOfTripleExprContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::oneOfTripleExpr()}.
	 * @param $context The parse tree.
	 */
	public function exitOneOfTripleExpr(Context\OneOfTripleExprContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::multiElementOneOf()}.
	 * @param $context The parse tree.
	 */
	public function enterMultiElementOneOf(Context\MultiElementOneOfContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::multiElementOneOf()}.
	 * @param $context The parse tree.
	 */
	public function exitMultiElementOneOf(Context\MultiElementOneOfContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::groupTripleExpr()}.
	 * @param $context The parse tree.
	 */
	public function enterGroupTripleExpr(Context\GroupTripleExprContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::groupTripleExpr()}.
	 * @param $context The parse tree.
	 */
	public function exitGroupTripleExpr(Context\GroupTripleExprContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::singleElementGroup()}.
	 * @param $context The parse tree.
	 */
	public function enterSingleElementGroup(Context\SingleElementGroupContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::singleElementGroup()}.
	 * @param $context The parse tree.
	 */
	public function exitSingleElementGroup(Context\SingleElementGroupContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::multiElementGroup()}.
	 * @param $context The parse tree.
	 */
	public function enterMultiElementGroup(Context\MultiElementGroupContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::multiElementGroup()}.
	 * @param $context The parse tree.
	 */
	public function exitMultiElementGroup(Context\MultiElementGroupContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::unaryTripleExpr()}.
	 * @param $context The parse tree.
	 */
	public function enterUnaryTripleExpr(Context\UnaryTripleExprContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::unaryTripleExpr()}.
	 * @param $context The parse tree.
	 */
	public function exitUnaryTripleExpr(Context\UnaryTripleExprContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::bracketedTripleExpr()}.
	 * @param $context The parse tree.
	 */
	public function enterBracketedTripleExpr(Context\BracketedTripleExprContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::bracketedTripleExpr()}.
	 * @param $context The parse tree.
	 */
	public function exitBracketedTripleExpr(Context\BracketedTripleExprContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::tripleConstraint()}.
	 * @param $context The parse tree.
	 */
	public function enterTripleConstraint(Context\TripleConstraintContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::tripleConstraint()}.
	 * @param $context The parse tree.
	 */
	public function exitTripleConstraint(Context\TripleConstraintContext $context) : void;
	/**
	 * Enter a parse tree produced by the `starCardinality`
	 * labeled alternative in {@see ShExDocParser::cardinality()}.
	 * @param $context The parse tree.
	 */
	public function enterStarCardinality(Context\StarCardinalityContext $context) : void;
	/**
	 * Exit a parse tree produced by the `starCardinality` labeled alternative
	 * in {@see ShExDocParser::cardinality()}.
	 * @param $context The parse tree.
	 */
	public function exitStarCardinality(Context\StarCardinalityContext $context) : void;
	/**
	 * Enter a parse tree produced by the `plusCardinality`
	 * labeled alternative in {@see ShExDocParser::cardinality()}.
	 * @param $context The parse tree.
	 */
	public function enterPlusCardinality(Context\PlusCardinalityContext $context) : void;
	/**
	 * Exit a parse tree produced by the `plusCardinality` labeled alternative
	 * in {@see ShExDocParser::cardinality()}.
	 * @param $context The parse tree.
	 */
	public function exitPlusCardinality(Context\PlusCardinalityContext $context) : void;
	/**
	 * Enter a parse tree produced by the `optionalCardinality`
	 * labeled alternative in {@see ShExDocParser::cardinality()}.
	 * @param $context The parse tree.
	 */
	public function enterOptionalCardinality(Context\OptionalCardinalityContext $context) : void;
	/**
	 * Exit a parse tree produced by the `optionalCardinality` labeled alternative
	 * in {@see ShExDocParser::cardinality()}.
	 * @param $context The parse tree.
	 */
	public function exitOptionalCardinality(Context\OptionalCardinalityContext $context) : void;
	/**
	 * Enter a parse tree produced by the `repeatCardinality`
	 * labeled alternative in {@see ShExDocParser::cardinality()}.
	 * @param $context The parse tree.
	 */
	public function enterRepeatCardinality(Context\RepeatCardinalityContext $context) : void;
	/**
	 * Exit a parse tree produced by the `repeatCardinality` labeled alternative
	 * in {@see ShExDocParser::cardinality()}.
	 * @param $context The parse tree.
	 */
	public function exitRepeatCardinality(Context\RepeatCardinalityContext $context) : void;
	/**
	 * Enter a parse tree produced by the `exactRange`
	 * labeled alternative in {@see ShExDocParser::repeatRange()}.
	 * @param $context The parse tree.
	 */
	public function enterExactRange(Context\ExactRangeContext $context) : void;
	/**
	 * Exit a parse tree produced by the `exactRange` labeled alternative
	 * in {@see ShExDocParser::repeatRange()}.
	 * @param $context The parse tree.
	 */
	public function exitExactRange(Context\ExactRangeContext $context) : void;
	/**
	 * Enter a parse tree produced by the `minMaxRange`
	 * labeled alternative in {@see ShExDocParser::repeatRange()}.
	 * @param $context The parse tree.
	 */
	public function enterMinMaxRange(Context\MinMaxRangeContext $context) : void;
	/**
	 * Exit a parse tree produced by the `minMaxRange` labeled alternative
	 * in {@see ShExDocParser::repeatRange()}.
	 * @param $context The parse tree.
	 */
	public function exitMinMaxRange(Context\MinMaxRangeContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::senseFlags()}.
	 * @param $context The parse tree.
	 */
	public function enterSenseFlags(Context\SenseFlagsContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::senseFlags()}.
	 * @param $context The parse tree.
	 */
	public function exitSenseFlags(Context\SenseFlagsContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::valueSet()}.
	 * @param $context The parse tree.
	 */
	public function enterValueSet(Context\ValueSetContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::valueSet()}.
	 * @param $context The parse tree.
	 */
	public function exitValueSet(Context\ValueSetContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::valueSetValue()}.
	 * @param $context The parse tree.
	 */
	public function enterValueSetValue(Context\ValueSetValueContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::valueSetValue()}.
	 * @param $context The parse tree.
	 */
	public function exitValueSetValue(Context\ValueSetValueContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::iriRange()}.
	 * @param $context The parse tree.
	 */
	public function enterIriRange(Context\IriRangeContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::iriRange()}.
	 * @param $context The parse tree.
	 */
	public function exitIriRange(Context\IriRangeContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::iriExclusion()}.
	 * @param $context The parse tree.
	 */
	public function enterIriExclusion(Context\IriExclusionContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::iriExclusion()}.
	 * @param $context The parse tree.
	 */
	public function exitIriExclusion(Context\IriExclusionContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::literalRange()}.
	 * @param $context The parse tree.
	 */
	public function enterLiteralRange(Context\LiteralRangeContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::literalRange()}.
	 * @param $context The parse tree.
	 */
	public function exitLiteralRange(Context\LiteralRangeContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::literalExclusion()}.
	 * @param $context The parse tree.
	 */
	public function enterLiteralExclusion(Context\LiteralExclusionContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::literalExclusion()}.
	 * @param $context The parse tree.
	 */
	public function exitLiteralExclusion(Context\LiteralExclusionContext $context) : void;
	/**
	 * Enter a parse tree produced by the `languageRangeFull`
	 * labeled alternative in {@see ShExDocParser::languageRange()}.
	 * @param $context The parse tree.
	 */
	public function enterLanguageRangeFull(Context\LanguageRangeFullContext $context) : void;
	/**
	 * Exit a parse tree produced by the `languageRangeFull` labeled alternative
	 * in {@see ShExDocParser::languageRange()}.
	 * @param $context The parse tree.
	 */
	public function exitLanguageRangeFull(Context\LanguageRangeFullContext $context) : void;
	/**
	 * Enter a parse tree produced by the `languageRangeAt`
	 * labeled alternative in {@see ShExDocParser::languageRange()}.
	 * @param $context The parse tree.
	 */
	public function enterLanguageRangeAt(Context\LanguageRangeAtContext $context) : void;
	/**
	 * Exit a parse tree produced by the `languageRangeAt` labeled alternative
	 * in {@see ShExDocParser::languageRange()}.
	 * @param $context The parse tree.
	 */
	public function exitLanguageRangeAt(Context\LanguageRangeAtContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::languageExclusion()}.
	 * @param $context The parse tree.
	 */
	public function enterLanguageExclusion(Context\LanguageExclusionContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::languageExclusion()}.
	 * @param $context The parse tree.
	 */
	public function exitLanguageExclusion(Context\LanguageExclusionContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::r_include()}.
	 * @param $context The parse tree.
	 */
	public function enterR_include(Context\R_includeContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::r_include()}.
	 * @param $context The parse tree.
	 */
	public function exitR_include(Context\R_includeContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::annotation()}.
	 * @param $context The parse tree.
	 */
	public function enterAnnotation(Context\AnnotationContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::annotation()}.
	 * @param $context The parse tree.
	 */
	public function exitAnnotation(Context\AnnotationContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::semanticAction()}.
	 * @param $context The parse tree.
	 */
	public function enterSemanticAction(Context\SemanticActionContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::semanticAction()}.
	 * @param $context The parse tree.
	 */
	public function exitSemanticAction(Context\SemanticActionContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::literal()}.
	 * @param $context The parse tree.
	 */
	public function enterLiteral(Context\LiteralContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::literal()}.
	 * @param $context The parse tree.
	 */
	public function exitLiteral(Context\LiteralContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::predicate()}.
	 * @param $context The parse tree.
	 */
	public function enterPredicate(Context\PredicateContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::predicate()}.
	 * @param $context The parse tree.
	 */
	public function exitPredicate(Context\PredicateContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::rdfType()}.
	 * @param $context The parse tree.
	 */
	public function enterRdfType(Context\RdfTypeContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::rdfType()}.
	 * @param $context The parse tree.
	 */
	public function exitRdfType(Context\RdfTypeContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::datatype()}.
	 * @param $context The parse tree.
	 */
	public function enterDatatype(Context\DatatypeContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::datatype()}.
	 * @param $context The parse tree.
	 */
	public function exitDatatype(Context\DatatypeContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::shapeExprLabel()}.
	 * @param $context The parse tree.
	 */
	public function enterShapeExprLabel(Context\ShapeExprLabelContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::shapeExprLabel()}.
	 * @param $context The parse tree.
	 */
	public function exitShapeExprLabel(Context\ShapeExprLabelContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::tripleExprLabel()}.
	 * @param $context The parse tree.
	 */
	public function enterTripleExprLabel(Context\TripleExprLabelContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::tripleExprLabel()}.
	 * @param $context The parse tree.
	 */
	public function exitTripleExprLabel(Context\TripleExprLabelContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::numericLiteral()}.
	 * @param $context The parse tree.
	 */
	public function enterNumericLiteral(Context\NumericLiteralContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::numericLiteral()}.
	 * @param $context The parse tree.
	 */
	public function exitNumericLiteral(Context\NumericLiteralContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::rdfLiteral()}.
	 * @param $context The parse tree.
	 */
	public function enterRdfLiteral(Context\RdfLiteralContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::rdfLiteral()}.
	 * @param $context The parse tree.
	 */
	public function exitRdfLiteral(Context\RdfLiteralContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::booleanLiteral()}.
	 * @param $context The parse tree.
	 */
	public function enterBooleanLiteral(Context\BooleanLiteralContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::booleanLiteral()}.
	 * @param $context The parse tree.
	 */
	public function exitBooleanLiteral(Context\BooleanLiteralContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::string()}.
	 * @param $context The parse tree.
	 */
	public function enterString(Context\StringContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::string()}.
	 * @param $context The parse tree.
	 */
	public function exitString(Context\StringContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::iri()}.
	 * @param $context The parse tree.
	 */
	public function enterIri(Context\IriContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::iri()}.
	 * @param $context The parse tree.
	 */
	public function exitIri(Context\IriContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::prefixedName()}.
	 * @param $context The parse tree.
	 */
	public function enterPrefixedName(Context\PrefixedNameContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::prefixedName()}.
	 * @param $context The parse tree.
	 */
	public function exitPrefixedName(Context\PrefixedNameContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::blankNode()}.
	 * @param $context The parse tree.
	 */
	public function enterBlankNode(Context\BlankNodeContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::blankNode()}.
	 * @param $context The parse tree.
	 */
	public function exitBlankNode(Context\BlankNodeContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::extension()}.
	 * @param $context The parse tree.
	 */
	public function enterExtension(Context\ExtensionContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::extension()}.
	 * @param $context The parse tree.
	 */
	public function exitExtension(Context\ExtensionContext $context) : void;
	/**
	 * Enter a parse tree produced by {@see ShExDocParser::restrictions()}.
	 * @param $context The parse tree.
	 */
	public function enterRestrictions(Context\RestrictionsContext $context) : void;
	/**
	 * Exit a parse tree produced by {@see ShExDocParser::restrictions()}.
	 * @param $context The parse tree.
	 */
	public function exitRestrictions(Context\RestrictionsContext $context) : void;
}