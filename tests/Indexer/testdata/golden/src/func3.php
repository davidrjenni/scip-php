  <?php
  
  declare(strict_types=1);
  
  namespace TestData4;
  
  use TestData\ClassF;
//    ^^^^^^^^^^^^^^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassF#
  
  /** @return ClassF */
  function fun2()
//         ^^^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData4/fun2().
//         documentation
//         > ```php
//         > function fun2()
//         > ```
//         documentation
//         > @return ClassF
  {
      return new ClassF();
//               ^^^^^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassF#
  }
  
