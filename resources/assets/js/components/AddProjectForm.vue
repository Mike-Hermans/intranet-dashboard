<template>
  <div>
    <div class="field">
      <label for="name" class="label">Project Name</label>
      <p class="control">
        <input type="text" class="input" :placeholder="name">
      </p>
    </div>

  </div>
</template>

<script>
  export default {
    data() {
      return {
        name: '',
        slug: '',
        key: ''
      }
    }
  }
</script>
