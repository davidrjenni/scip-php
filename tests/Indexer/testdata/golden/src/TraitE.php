  <?php
  
  declare(strict_types=1);
  
  namespace TestData;
  
  use Test\Dep\ClassI;
//    ^^^^^^^^^^^^^^^ reference scip-php composer davidrjenni/scip-php-test-dep 3e11662443768bf3887b227b8510bc789ed151c6 Test/Dep/ClassI#
  
  trait TraitE
//      ^^^^^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/TraitE#
//      documentation ```php
  {
      public int $e1;
//               ^^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/TraitE#$e1.
//               documentation ```php
  
      /** @var ClassI */
      public $e2;
//           ^^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/TraitE#$e2.
//           documentation ```php
//           documentation @var ClassI
  
      protected function e1(): bool
//                       ^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/TraitE#e1().
//                       documentation ```php
      {
          return $this->e2->i1;
//                      ^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/TraitE#$e2.
//                          ^^ reference scip-php composer davidrjenni/scip-php-test-dep 3e11662443768bf3887b227b8510bc789ed151c6 Test/Dep/ClassI#$i1.
      }
  
      public function e2(): int
//                    ^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/TraitE#e2().
//                    documentation ```php
      {
          $v1 = ClassI::I1;
//              ^^^^^^ reference scip-php composer davidrjenni/scip-php-test-dep 3e11662443768bf3887b227b8510bc789ed151c6 Test/Dep/ClassI#
//                      ^^ reference scip-php composer davidrjenni/scip-php-test-dep 3e11662443768bf3887b227b8510bc789ed151c6 Test/Dep/ClassI#I1.
          return $this->e2::I1 * $v1;
//                      ^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/TraitE#$e2.
//                          ^^ reference scip-php composer davidrjenni/scip-php-test-dep 3e11662443768bf3887b227b8510bc789ed151c6 Test/Dep/ClassI#I1.
      }
  
      public function e3(): int
//                    ^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/TraitE#e3().
//                    documentation ```php
      {
          if (true) {
              return 23 - count([0]);
//                        ^^^^^ reference scip-php composer php 8.2.10 count().
          }
          if (false) {
              return 42;
          }
          return -1;
      }
  }
  
