<?php

namespace Kanboard\Controller;

use Kanboard\Core\ObjectStorage\ObjectStorageException;

/**
 * File Viewer Controller
 *
 * @package  controller
 * @author   Frederic Guillot
 */
class FileViewer extends Base
{
    /**
     * Get file content from object storage
     *
     * @access private
     * @param  array $file
     * @return string
     */
    private function getFileContent(array $file)
    {
        $content = '';

        try {

            if ($file['is_image'] == 0) {
                $content = $this->objectStorage->get($file['path']);
            }

        } catch (ObjectStorageException $e) {
            $this->logger->error($e->getMessage());
        }

        return $content;
    }

    /**
     * Show file content in a popover
     *
     * @access public
     */
    public function show()
    {
        $file = $this->getFile();
        $type = $this->helper->file->getPreviewType($file['name']);
        $params = array('file_id' => $file['id'], 'project_id' => $this->request->getIntegerParam('project_id'));

        if ($file['model'] === 'taskFile') {
            $params['task_id'] = $file['task_id'];
        }

        $this->response->html($this->template->render('file_viewer/show', array(
            'file' => $file,
            'params' => $params,
            'type' => $type,
            'content' => $this->getFileContent($file),
        )));
    }

    /**
     * Display image
     *
     * @access public
     */
    public function image()
    {
        try {
            $file = $this->getFile();
            $this->response->contentType($this->helper->file->getImageMimeType($file['name']));
            $this->objectStorage->output($file['path']);
        } catch (ObjectStorageException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Display image thumbnail
     *
     * @access public
     */
    public function thumbnail()
    {
        $this->response->contentType('image/jpeg');

        try {
            $file = $this->getFile();
            $model = $file['model'];
            $this->objectStorage->output($this->$model->getThumbnailPath($file['path']));
        } catch (ObjectStorageException $e) {
            $this->logger->error($e->getMessage());

            // Try to generate thumbnail on the fly for images uploaded before Kanboard < 1.0.19
            $data = $this->objectStorage->get($file['path']);
            $this->$model->generateThumbnailFromData($file['path'], $data);
            $this->objectStorage->output($this->$model->getThumbnailPath($file['path']));
        }
    }

    /**
     * File download
     *
     * @access public
     */
    public function download()
    {
        try {
            $file = $this->getFile();
            $this->response->forceDownload($file['name']);
            $this->objectStorage->output($file['path']);
        } catch (ObjectStorageException $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
