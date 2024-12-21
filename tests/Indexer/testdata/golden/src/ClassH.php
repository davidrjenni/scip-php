  <?php
  
  declare(strict_types=1);
  
  namespace TestData
  {
      use Exception;
//        ^^^^^^^^^ reference scip-php composer php 8.3.14 Exception#
  
      final class ClassH extends Exception
//                ^^^^^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassH#
//                documentation
//                > ```php
//                > final class ClassH extends Exception
//                > ```
//                               ^^^^^^^^^ reference scip-php composer php 8.3.14 Exception#
      {
  
          public function __construct()
//                        ^^^^^^^^^^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassH#__construct().
//                        documentation
//                        > ```php
//                        > public function __construct()
//                        > ```
          {
              parent::__construct();
//            ^^^^^^ reference scip-php composer php 8.3.14 Exception#
//                    ^^^^^^^^^^^ reference scip-php composer php 8.3.14 Exception#__construct().
          }
  
          public function h1(): int
//                        ^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassH#h1().
//                        documentation
//                        > ```php
//                        > public function h1(): int
//                        > ```
          {
              $x = \TestData4\fun2()->f2()->a2();
//                 ^^^^^^^^^^^^^^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData4/fun2().
//                                    ^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassF#f2().
//                                          ^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassA#a2().
              return $this->getCode() + fun2()->a2() * $x;
//                          ^^^^^^^ reference scip-php composer php 8.3.14 Exception#getCode().
//                                      ^^^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 fun2().
//                                              ^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassA#a2().
          }
      }
  }
  
  // https://www.php.net/manual/en/language.namespaces.definitionmultiple.php
  
  namespace TestData2
  {
      final class ClassJ
//                ^^^^^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData2/ClassJ#
//                documentation
//                > ```php
//                > final class ClassJ
//                > ```
      {
          public const J2 = 42;
//                     ^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData2/ClassJ#J2.
//                     documentation
//                     > ```php
//                     > public J2 = 42
//                     > ```
      }
  }
  
  namespace
  {
      final class ClassJ
//                ^^^^^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 ClassJ#
//                documentation
//                > ```php
//                > final class ClassJ
//                > ```
      {
          public const J3 = 42;
//                     ^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 ClassJ#J3.
//                     documentation
//                     > ```php
//                     > public J3 = 42
//                     > ```
      }
  }
  
