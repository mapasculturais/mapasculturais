<?php
namespace MapasCulturais\Traits;

use Exception;
use \MapasCulturais\App;
use MapasCulturais\Exceptions\SaveFileError;
use MapasCulturais\i;

/**
 * Defines that the controller has uploads.
 *
 * Use this trait in controllers of entities that has files (uses the trait EntityFiles) to automaticaly handle uploads.
 */
trait ControllerUploads{

    /**
     * Handle file uploads and creates the File Entities associating them to the owner of the id.
     *
     * You can upload files with description.
     *
     * For example, if you want to change the avatar of the agent with id 99 you need to post the below form:
     * <code>
     * <form method="post" action="/agent/upload/id:99" enctype="multipart/form-data">
     *      <input type="file" name="avatar" />
     * </form>
     * </code>
     *
     * Example of form to upload a file with description:
     * <code>
     * <form method="post" action="/agent/upload/id:99" enctype="multipart/form-data">
     *      <input type="file" name="download" />
     *      <input type="text" name="description[downloads]" />
     * </form>
     * </code>
     *
     * This action response a json with uploaded files info or errors.
     *
     * Example of an error response:
     * <code>
     * [{"error":"The uploaded file is not a valid image."}]
     * </code>
     *
     * Example of a successfull avatar upload response:
     * <code>
     * {"avatar":{"url":"http:\/\/mapasculturais.domain/files/filesuploaded.jpg","description":null}}
     * </code>
     *
     *
     * @see http://wideimage.sourceforge.net/documentation/manipulating-images/
     *
     */
    function POST_upload(){
        /**
         * @todo Melhores Mensagens de erro
         */
        $this->requireAuthentication();
        
        $owner = $this->requestedEntity;
        
        if(!$owner){
            $this->errorJson(\MapasCulturais\i::__('O dono não existe'));
            return;
        }

        $file_class_name = $owner->getFileClassName();
        
        $app = App::i();

        // if no files uploaded or no id in request data, return an error
        if(empty($_FILES) || !$this->data['id']){
            $this->errorJson(\MapasCulturais\i::__('Nenhum arquivo enviado'));
            return ;
        }

        $result = [];
        $files = [];

        // the group of the files is the key in $_FILES array
        foreach(array_keys($_FILES) as $group_name){
            $ext = pathinfo($_FILES[$group_name]['name'], PATHINFO_EXTENSION);
            $name = pathinfo($_FILES[$group_name]['name'], PATHINFO_FILENAME);
            $_FILES[$group_name]['name'] = $app->slugify($name).".".$ext;
            
//            $this->errorJson('asd '.$this->id.' '.$group_name.' '.$app->getRegisteredFileGroup($this->id, $group_name));
            $upload_group = $app->getRegisteredFileGroup($this->id, $group_name);
            // if the group exists
            if($upload_group = $app->getRegisteredFileGroup($this->id, $group_name)){
                try {
                    $file = $app->handleUpload($group_name, $file_class_name);
                    // if multiple files was uploaded and this group is unique, don't save this group of files.
                    if(is_array($file) && $upload_group->unique){
                        continue;

                    // else if multiple files was uploaded and this group accepts multiple files, set the group to this files and add them to $files array
                    }elseif(is_array($file) && !$upload_group->unique){
                        foreach($file as $f){
                            if($error = $upload_group->getError($f)){
                                $files[] = ['error' => $error, 'group' => $upload_group];
                            }else{
                                $f->group = $group_name;
                                $files[] = $f;
                            }
                        }

                    // else if a single file was uploaded, add the group to this file and add this file to $files array
                    }else{
                        if(key_exists('description', $this->data) && is_array($this->data['description']) && key_exists($group_name, $this->data['description']))
                            $file->description = $this->data['description'][$group_name];

                        if($errors = $file->getValidationErrors()){                            
                            $error_messages = [];
                            foreach($errors as $_errors){
                                $error_messages = array_merge(array_values($_errors), $error_messages);
                            }
                            $files[$group_name] = ['error' => implode(', ', $error_messages), 'group' => $upload_group];

                        } else if ($error = $upload_group->getError($file)){
                            $files[] = ['error' => $error, 'group' => $upload_group];
                        }else{
                            $file->group = $group_name;
                            $files[] = $file;
                        }
                    }

                }catch(\MapasCulturais\Exceptions\FileUploadError $e){
                    $files[] = [
                        'error' => $e->message, 
                        'group' => $upload_group
                    ];
                }
            }
        }

        // if no files was added to $files array, return an error
        if(empty($files)){
            $this->errorJson(\MapasCulturais\i::__('nenhum arquivo válido enviado'));
            return;
        }else{
            $all_files_contains_error = true;
            foreach($files as $f){
                if(is_object($f)){
                    $all_files_contains_error = false;
                    break;
                }
            }

            if($all_files_contains_error){
                $result = [];
                foreach($files as $error)
                    if(key_exists('group',$error) && $error['group']->unique)
                        $result[$error['group']->name] = $error['error'];
                    else{
                        if(!isset($result[$error['group']->name])){
                            $result[$error['group']->name] = [];
                        }
                        $result[$error['group']->name][] = $error['error'];
                    }
                $this->errorJson($result);
                return;
            }
        }

        foreach($files as $file){
            $upload_group = $app->getRegisteredFileGroup($this->id, $file->group);
            $old_files = $app->repo($file_class_name)->findBy(['owner' => $owner, 'group' => $file->group]);

            $file->owner = $owner;

            try{
                $app->applyHookBoundTo($this, "{$file->hookPrefix}.upload.filesSave:before", [$file]);
                $file->save();
                $app->applyHookBoundTo($this, "{$file->hookPrefix}.upload.filesSave:after", [$file]);
            } catch (SaveFileError $e) {
                $this->errorJson([$file->group => $e->getMessage()]);
            }
            $file_group = $file->group;

            if($upload_group->unique){
                if($old_files) {
                    foreach($old_files as $old_file) {
                        if($old_file->id != $file->id) {
                            $old_file->delete(true);
                        }
                    }
                }

                $result[$file_group] = $file;
            }else{
                if(!key_exists($file->group, $result))
                        $result[$file->group] = [];
                $result[$file_group][] = $file;
            }
        }

        $app->em->flush();
        $this->json($result);
        return;
    }

    /**
     * This controller uses Uploads
     * @return bool true
     */
    public static function usesUploads(){
        return true;
    }
}
