Installation
============

Step 1: Download the Bundle
---------------------------

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

.. code-block:: terminal

    $ composer require openwines/computer-vision-bundle "~1"

This command requires you to have Composer installed globally, as explained
in the `installation chapter`_ of the Composer documentation.

Step 2: Enable the Bundle
-------------------------

Then, enable the bundle by adding it to the list of registered bundles
in the ``app/AppKernel.php`` file of your project:

.. code-block:: php

    <?php
    // app/AppKernel.php

    // ...
    class AppKernel extends Kernel
    {
        public function registerBundles()
        {
            $bundles = array(
                // ...

                new OpenWines\ComputerVisionBundle\OpenWinesComputerVisionBundle(),
            );

            // ...
        }

        // ...
    }

Then create your API key: https://www.microsoft.com/cognitive-services/en-us/computer-vision-api

And copy it in `app/config/parameters.yml`:

.. code-block:: yml
    parameters:
        (...)
        microsoft_computer_vision.api_token: myToken

.. _`installation chapter`: https://getcomposer.org/doc/00-intro.md