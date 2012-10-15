<?php
/**
 * Copyright (C) 2012 Derek J. Lambert
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace CrEOF\Spatial\ORM\Query;

use CrEOF\Spatial\ORM\Query\AST\Functions\ReturnsWKBInterface;
use CrEOF\Spatial\ORM\Query\AST\Functions\ReturnsWKTInterface;
use Doctrine\ORM\Query\AST\SelectExpression;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\SqlWalker;

/**
 * GeometryWalker
 *
 * Custom DQL AST walker to return geometry objects from queries instead of strings.
 *
 * @author  Derek J. Lambert <dlambert@dereklambert.com>
 * @license http://dlambert.mit-license.org MIT
 */
class GeometryWalker extends SqlWalker
{
    /**
     * Walks down a SelectExpression AST node and generates the corresponding SQL.
     *
     * @param SelectExpression $selectExpression
     *
     * @return string The SQL.
     */
    public function walkSelectExpression($selectExpression)
    {
        $expr   = $selectExpression->expression;
        $hidden = $selectExpression->hiddenAliasResultVariable;

        if ($expr instanceof ReturnsWKBInterface || $expr instanceof ReturnsWKTInterface) {
            $columnAlias = $this->getSQLColumnAlias('sclr');
            $resultAlias = $selectExpression->fieldIdentificationVariable ?: $this->scalarResultCounter++;

            $this->scalarResultAliasMap[$resultAlias] = $columnAlias;

            if ( ! $hidden) {
                $this->rsm->addScalarResult($columnAlias, $resultAlias, 'geometry');
            }

            return sprintf('%s AS %s', $expr->dispatch($this), $columnAlias);
        }

        return parent::walkSelectExpression($selectExpression);
    }
}
