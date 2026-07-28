<?php
namespace MediaText\Job;

use Omeka\Job\AbstractJob;

class UpdateCompression extends AbstractJob
{
    public function perform()
    {
        $services = $this->getServiceLocator();
        $connection = $services->get('Omeka\Connection');
        $logger = $services->get('Omeka\Logger');
        $settings = $services->get('Omeka\Settings');
        $em = $services->get('Omeka\EntityManager');

        $logger->info('Job started');

        $compressionLevel = $settings->get('mediatext_compression_level', false);
        $logger->info(sprintf('Compression level is %d', $compressionLevel));

        $em->flush();

        $stmt = $connection->prepare('select id, compressed_content from mediatext_representation where id > :lastId limit 1');

        $lastId = 0;
        $updated = 0;
        while (1) {
            if ($this->shouldStop()) {
                $logger->info('Job stopped');
                return;
            }

            $stmt->bindValue('lastId', $lastId);
            $result = $stmt->executeQuery();
            $row = $result->fetchAssociative();
            if (!$row) {
                break;
            }

            $lastId = $row['id'];

            $data = [
                'compressed_content' => gzencode(gzdecode($row['compressed_content']), $compressionLevel),
            ];

            $connection->update('mediatext_representation', $data, ['id' => $row['id']]);

            $updated++;

            if ($updated % 100 === 0) {
                $logger->info(sprintf('Number of updated textual representations so far: %d', $updated));
                $em->flush();
            }
        }

        $logger->info(sprintf('Total number of updated textual representations: %d', $updated));

        $logger->info('Job ended normally');
    }
}
