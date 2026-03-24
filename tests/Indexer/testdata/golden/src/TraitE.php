  <?php
  
  declare(strict_types=1);
  
  namespace TestData;
  
  use Test\Dep\ClassI;
//    ^^^^^^^^^^^^^^^ reference scip-php composer davidrjenni/scip-php-test-dep 3e11662443768bf3887b227b8510bc789ed151c6 Test/Dep/ClassI#
  
//⌄ enclosing_range_start scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/TraitE#
  trait TraitE
//      ^^^^^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/TraitE#
//      documentation
//      > ```php
//      > trait TraitE
//      > ```
  {
//    ⌄ enclosing_range_start scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/TraitE#$e1.
      public int $e1;
//               ^^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/TraitE#$e1.
//               documentation
//               > ```php
//               > public int $e1
//               > ```
//                  ⌃ enclosing_range_end scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/TraitE#$e1.
  
//    ⌄ enclosing_range_start scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/TraitE#$e2.
      /** @var ClassI */
      public $e2;
//           ^^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/TraitE#$e2.
//           documentation
//           > ```php
//           > public $e2
//           > ```
//           documentation
//           > @var ClassI
//              ⌃ enclosing_range_end scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/TraitE#$e2.
  
//    ⌄ enclosing_range_start scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/TraitE#e1().
      protected function e1(): bool
//                       ^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/TraitE#e1().
//                       documentation
//                       > ```php
//                       > protected function e1(): bool
//                       > ```
      {
          return $this->e2->i1;
//                      ^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/TraitE#$e2.
//                          ^^ reference scip-php composer davidrjenni/scip-php-test-dep 3e11662443768bf3887b227b8510bc789ed151c6 Test/Dep/ClassI#$i1.
      }
//    ⌃ enclosing_range_end scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/TraitE#e1().
  
//    ⌄ enclosing_range_start scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/TraitE#e2().
      public function e2(): int
//                    ^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/TraitE#e2().
//                    documentation
//                    > ```php
//                    > public function e2(): int
//                    > ```
      {
          $v1 = ClassI::I1;
//              ^^^^^^ reference scip-php composer davidrjenni/scip-php-test-dep 3e11662443768bf3887b227b8510bc789ed151c6 Test/Dep/ClassI#
//                      ^^ reference scip-php composer davidrjenni/scip-php-test-dep 3e11662443768bf3887b227b8510bc789ed151c6 Test/Dep/ClassI#I1.
          return $this->e2::I1 * $v1;
//                      ^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/TraitE#$e2.
//                          ^^ reference scip-php composer davidrjenni/scip-php-test-dep 3e11662443768bf3887b227b8510bc789ed151c6 Test/Dep/ClassI#I1.
      }
//    ⌃ enclosing_range_end scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/TraitE#e2().
  
//    ⌄ enclosing_range_start scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/TraitE#e3().
      public function e3(): int
//                    ^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/TraitE#e3().
//                    documentation
//                    > ```php
//                    > public function e3(): int
//                    > ```
      {
          if (true) {
              return 23 - count([0]);
//                        ^^^^^ reference scip-php composer php 8.3.19 count().
          }
          if (false) {
              return 42;
          }
          return -1;
      }
//    ⌃ enclosing_range_end scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/TraitE#e3().
  }
//⌃ enclosing_range_end scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/TraitE#
  
