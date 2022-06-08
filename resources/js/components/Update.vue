<template>
    <div>
        <div id="header">
            <div>Dropzone Images</div>
            <div><a v-bind:href="'/'">Add New Post</a></div>
        </div>

        <div class="dz" >
            <input v-model="title" type="text" class="form-control" placeholder="Title Name">
            <div ref="dropzone">
                Uploads or Drop Files
            </div>
        </div>

        <div class="mb-3 editor">
            <vue-editor useCustomImageHandler @image-removed="handleImageRemoved" @image-added="handleImageAdded" v-model="content" />
        </div>

        <input :disabled="!isDisabled" @click.prevent="update" type="submit" class="btn btn-primary" value="Update">

        <div class="mt-5 images" v-if="post">
            <hr>
            <h4>{{ post.title }}</h4>
            <div v-for="image in post.images">
                <img :src="image.preview_url" class="mb-3" alt=""/>
                <img :src="image.url" class="mb-3" alt=""/>
                <hr>
            </div>
            <div class="ql-editor" v-html="post.content"></div>
        </div>
    </div>
</template>

<script>
import Dropzone from 'dropzone'
import { VueEditor } from "vue2-editor";

export default {
    name: "Update",

    data(){
        return{
            dropzone: null,
            title: null,
            post: null,
            content: null,
            imagesIdsForDelete: [],
            imagesUrlsForDelete: [],
        }
    },

    mounted() {
        this.dropzone = new Dropzone(this.$refs.dropzone, {
            url: '/api/image/update',
            autoProcessQueue: false,
            addRemoveLinks: true
        })

        this.dropzone.on('removedfile', (file) => {
            this.imagesIdsForDelete.push(file.id)
        })

        this.getPosts()
    },

    updated() {
       // this.isDisabled()
    },

    methods:{
        update(){
            const data = new FormData()
            const files = this.dropzone.getAcceptedFiles()
            files.forEach(file => {
                data.append('images[]', file)
                this.dropzone.removeFile(file)
            })

            this.imagesIdsForDelete.forEach(idForDelete => {
                data.append('image_ids_for_delete[]', idForDelete)
            })

            this.imagesUrlsForDelete.forEach(urlForDelete => {
                data.append('image_urls_for_delete[]', urlForDelete)
            })

            data.append('title', this.title)
            data.append('content', this.content)
            data.append('_method', 'PATCH')

            this.title = ''
            this.content = ''
            axios.post(`/api/image/update/${this.post.id}`, data)
                .then(res => {
                    this.getPosts()
                })
        },

        getPosts(){
            axios.get('/api/image/get')
                .then(res =>{
                    this.post = res.data.data

                    this.title = this.post.title
                    this.content = this.post.content

                    this.post.images.forEach(image => {

                        let file = {id: image.id, name: image.name, size: image.size };
                        this.dropzone.displayExistingFile(file, image.preview_url);

                    })

                })
        },

        handleImageAdded(file, Editor, cursorLocation, resetUploader) {
            const formData = new FormData();
            formData.append("image", file);

            axios.post('/api/image/editor', formData)
                .then(result => {
                    const url = result.data.url; // Get url from response
                    Editor.insertEmbed(cursorLocation, "image", url);
                    resetUploader();
                })
                .catch(err => {
                    console.log(err);
                });
        },

        handleImageRemoved(url){
            this.imagesUrlsForDelete.push(url)
        },

      isDisabled(){
            console.log(document.getElementsByClassName('dz-preview'));
        }

    },

    components:{
        VueEditor
    }


}
</script>

<style >
    div#header{width: 33%; padding-left: 50px; margin-bottom: 20px; padding-top: 20px; display: flex; justify-content: space-between; text-align: center; align-items: center;}
    div#header div:first-child{font-size: 30px;}
    div.dz input, div.editor{width: 30%; margin-left: 50px;}
    input[type=submit]{margin-left: 50px;}
    div.dz > div{padding: 80px 30px; width: 30%; margin: 20px 0 20px 50px; cursor: pointer; background-color: #2d3748; border: 1px solid #fff;
        color: #fff; border-radius: 5px; text-align: center;}
    div.dz > div:hover{background-color: #0c63e4; color: #fff;}
    div.dz > div a.dz-remove{color: red; margin-bottom: 20px; display:block;}
    div.dz > div a.dz-remove:hover{color: #fff;}

    div.images{margin-left: 50px; width: 30%;}
    div.images h4{text-align: center;}
    div.images img{display: block; margin: 5px; max-width: 100%;}

    div.dz-success-mark, div.dz-error-mark{display: none;}
</style>
