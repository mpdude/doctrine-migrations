<?php
/*
 *  $Id$
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information, see
 * <http://www.doctrine-project.org>.
 */
 
namespace DoctrineExtensions\Migrations\Tools\Console\Command;

use Symfony\Components\Console\Command\Command,
    Symfony\Components\Console\Input\InputInterface,
    Symfony\Components\Console\Output\OutputInterface,
    DoctrineExtensions\Migrations\Migration,
    DoctrineExtensions\Migrations\MigrationException,
    DoctrineExtensions\Migrations\OutputWriter,
    DoctrineExtensions\Migrations\Configuration\Configuration,
    DoctrineExtensions\Migrations\Configuration\YamlConfiguration,
    DoctrineExtensions\Migrations\Configuration\XmlConfiguration;

/**
 * CLI Command for adding and deleting migration versions from the version table.
 *
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link    www.doctrine-project.org
 * @since   2.0
 * @version $Revision$
 * @author  Jonathan Wage <jonwage@gmail.com>
 */
abstract class AbstractCommand extends Command
{
    protected $_configuration;

    protected function _outputHeader(Configuration $configuration, OutputInterface $output)
    {
        $name = $configuration->getName();
        $name = $name ? $name : 'Doctrine Database Migrations';
        $name = str_repeat(' ', 20) . $name . str_repeat(' ', 20);
        $output->writeln('<question>' . str_repeat(' ', strlen($name)) . '</question>');
        $output->writeln('<question>' . $name . '</question>');
        $output->writeln('<question>' . str_repeat(' ', strlen($name)) . '</question>');
        $output->writeln('');
    }

    protected function _getMigrationConfiguration(InputInterface $input, OutputInterface $output)
    {
        if ( ! $this->_configuration) {
            $outputWriter = new OutputWriter(function($message) use ($output) {
                return $output->writeln($message);
            });

            $em = $this->getHelper('em')->getEntityManager();

            if ($input->getOption('configuration')) {
                $info = pathinfo($input->getOption('configuration'));
                $class = $info['extension'] === 'xml' ? 'DoctrineExtensions\Migrations\Configuration\XmlConfiguration' : 'DoctrineExtensions\Migrations\Configuration\YamlConfiguration';
                $configuration = new $class($em->getConnection(), $outputWriter);
                $configuration->load($input->getOption('configuration'));
            } else if (file_exists('migrations.xml')) {
                $configuration = new XmlConfiguration($em->getConnection(), $outputWriter);
                $configuration->load('migrations.xml');
            } else if (file_exists('migrations.yml')) {
                $configuration = new YamlConfiguration($em->getConnection(), $outputWriter);
                $configuration->load('migrations.yml');
            } else {
                $configuration = new Configuration($em->getConnection(), $outputWriter);
            }
            $this->_configuration = $configuration;
        }
        return $this->_configuration;
    }
}