<template>
    <div id="app">
        <div class="upload-example">
            <Cropper
                ref="cropper"
                class="upload-example-cropper"
                :src="image"
            />
            <div class="button-wrapper">
                <span class="button" @click="$refs.file.click()">
                    <input
                        type="file"
                        ref="file"
                        @change="uploadImage($event)"
                        accept="image/*"
                    />
                    Upload image
                </span>
                <span class="button" @click="cropImage">Crop image</span>
            </div>
        </div>
    </div>
</template>