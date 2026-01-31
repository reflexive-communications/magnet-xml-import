<?php

namespace Civi\MagnetXmlImport;

use Civi\RcBase\ApiWrapper\Create;
use Civi\Test;
use Civi\Test\HeadlessInterface;
use PHPUnit\Framework\TestCase;

/**
 * @group headless
 */
class HeadlessTestCase extends TestCase implements HeadlessInterface
{
    /**
     * Apply a forced rebuild of DB, thus
     * create a clean DB before running tests
     *
     * @throws \Civi\RcBase\Exception\APIException
     */
    public static function setUpBeforeClass(): void
    {
        // Resets DB
        Test::headless()
            ->install(['rc-base', 'magnet-xml-import'])
            ->apply(true);

        // Create custom group and field
        $custom_group_id = Create::entity('CustomGroup', ['title' => 'bank', 'extends' => 'Contact']);
        Create::entity('CustomField', ['custom_group_id' => $custom_group_id, 'label' => 'bank_account_number', 'html_type' => 'Text']);
    }

    /**
     * @return void
     */
    public function setUpHeadless(): void
    {
    }
}
