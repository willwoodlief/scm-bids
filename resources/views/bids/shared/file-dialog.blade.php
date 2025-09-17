@include('shared.documents.add-file-dialog',
                            [
                                'form_url'=>route('scm-bid.files.create',['single_bid'=>$bid->id]),
                                'drag_url'=>route('scm-bid.files.add',['single_bid'=>$bid->id]),
                                'file_type'=>'bid_files',
                                'file_owner'=>$bid,
                                'max_file_size'=>\App\Models\ProjectFile::DEFAULT_MAX_FILE_SIZE_BYTES
                            ])


<script>
    function scmAppendDataToDropzoneUpload(myDropzone,file, xhr, formData) {

    }
</script>
