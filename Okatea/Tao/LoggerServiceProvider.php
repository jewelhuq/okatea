<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\FirePHPHandler;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\WebProcessor;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\MemoryPeakUsageProcessor;

class LoggerServiceProvider implements ServiceProviderInterface
{
	public function register(Container $okt)
	{
		$okt['logger'] = function($okt) {
			return new Logger('okatea', [
					new FirePHPHandler()
				],
				[
					new IntrospectionProcessor(),
					new WebProcessor(),
					new MemoryUsageProcessor(),
					new MemoryPeakUsageProcessor()
				]
			);
		};

		$okt['phpLogger'] = function($okt) {
			return new Logger('php_error',
				[
					new FingersCrossedHandler(
						new StreamHandler(
							$okt['logs_dir'] . '/php_errors.log',
							Logger::INFO
						),
						Logger::WARNING
					)
				],
				[
					new IntrospectionProcessor(),
					new WebProcessor(),
					new MemoryUsageProcessor(),
					new MemoryPeakUsageProcessor()
				]
			);
		};
	}
}
