<template>
  <div class="sidebar-logo-container" :class="{'collapse':collapse}">
    <transition name="sidebarLogoFade">
      <router-link v-if="collapse" key="collapse" class="p-3 sidebar-logo-link" to="/">
        <img  v-if="logo" :src="logo" class="sidebar-logo">
        <h1 v-else class="sidebar-title">{{settings.company_name}} </h1>
      </router-link>
      <router-link v-else key="expand" class="sidebar-logo-link" to="/">
        <div class="flex items-start">
          <div class=" p-2 w-1/8" v-if="logo">
            <img :src="logo" class="sidebar-logo ">
          </div>
          <div class=" w-7/8 align-top" >
            <h1  class="sidebar-title align-top">{{settings.company_name}} </h1>
          </div>
        </div>
      </router-link>
    </transition>
  </div>
</template>


<script>
import logo from '@/assets/images/hader_logo.png';
import { getPublicSettings } from '@/api/user/settings';

export default {
  name: 'SidebarLogo',
  props: {
    collapse: {
      type: Boolean,
      required: true,
    },
  },
  data() {
    return {
      settings: {},
      title: 'MLM World',
      logo: logo,
    };
  },
  created() {
    getPublicSettings().then(response => {
      this.settings = response.data;
    });
  },
};
</script>

<style lang="scss" scoped>
.sidebarLogoFade-enter-active {
  transition: opacity 1.5s;
}

.sidebarLogoFade-enter,
.sidebarLogoFade-leave-to {
  opacity: 0;
}

.sidebar-logo-container {
  position: relative;
  width: 100%;
  height: 50px;
  line-height: 50px;
  background: #2b2f3a;
  text-align: center;
  overflow: hidden;

  & .sidebar-logo-link {
    height: 100%;
    width: 100%;

    & .sidebar-logo {
      width: 32px;
      height: 32px;
      vertical-align: middle;
      margin-right: 12px;
    }

    & .sidebar-title {
      display: inline-block;
      margin: 0;
      color: #fff;
      font-weight: 600;
      line-height: 50px;
      font-size: 14px;
      font-family: Avenir, Helvetica Neue, Arial, Helvetica, sans-serif;
      vertical-align: middle;
    }
  }

  &.collapse {
    .sidebar-logo {
      margin-right: 0px;
    }
  }
}
</style>
