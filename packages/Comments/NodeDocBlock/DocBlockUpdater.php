<?php

declare(strict_types=1);

namespace Rector\Comments\NodeDocBlock;

use PhpParser\Comment;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\Printer\PhpDocInfoPrinter;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class DocBlockUpdater
{
    public function __construct(
        private readonly PhpDocInfoPrinter $phpDocInfoPrinter
    ) {
    }

    public function updateNodeWithPhpDocInfo(Node $node): void
    {
        // nothing to change? don't save it
        $phpDocInfo = $this->resolveChangedPhpDocInfo($node);
        if (! $phpDocInfo instanceof PhpDocInfo) {
            return;
        }

        $phpDoc = $this->printPhpDocInfoToString($phpDocInfo);

        // make sure, that many separated comments are not removed
        if ($phpDoc === '') {
            $this->setCommentsAttribute($node);

            return;
        }

        // this is needed to remove duplicated // commentsAsText
        $node->setDocComment(new Doc($phpDoc));
    }

    public function updateRefactoredNodeWithPhpDocInfo(Node $node): void
    {
        // nothing to change? don't save it
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        if (! $phpDocInfo instanceof PhpDocInfo) {
            return;
        }

        $phpDocNode = $phpDocInfo->getPhpDocNode();
        if ($phpDocNode->children === []) {
            $this->setCommentsAttribute($node);
            return;
        }

        $printedPhpDoc = $this->printPhpDocInfoToString($phpDocInfo);
        $node->setDocComment(new Doc($printedPhpDoc));
    }

    private function setCommentsAttribute(Node $node): void
    {
        $comments = array_filter($node->getComments(), static fn (Comment $comment): bool => ! $comment instanceof Doc);
        $node->setAttribute(AttributeKey::COMMENTS, $comments);
    }

    private function resolveChangedPhpDocInfo(Node $node): ?PhpDocInfo
    {
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        if (! $phpDocInfo instanceof PhpDocInfo) {
            return null;
        }

        if (! $phpDocInfo->hasChanged()) {
            return null;
        }

        return $phpDocInfo;
    }

    private function printPhpDocInfoToString(PhpDocInfo $phpDocInfo): string
    {
        if ($phpDocInfo->isNewNode()) {
            return $this->phpDocInfoPrinter->printNew($phpDocInfo);
        }

        return $this->phpDocInfoPrinter->printFormatPreserving($phpDocInfo);
    }
}
