<template>
  <div class="navbar">
    <hamburger id="hamburger-container" :is-active="sidebar.opened" class="hamburger-container" @toggleClick="toggleSideBar" />

    <breadcrumb id="breadcrumb-container" class="breadcrumb-container" />

    <div class="right-menu">
      <template>
        <div class="right-menu-item hover-effect" id="google_translate_element"></div>
      </template>
      <template>
        <!-- <search id="header-search" class="right-menu-item" /> -->

        <el-badge v-if="roles.includes('user')" :value="cartCount" class="item" style="margin-right: 20px;">
          <router-link to="/shopping/cart"><i class="fas fa-shopping-cart cart-btn"" /></router-link>
        </el-badge>

      </template>
      

      <template v-if="device!=='mobile'">
        <!-- <search id="header-search" class="right-menu-item" /> -->

        <!-- <screenfull id="screenfull" class="right-menu-item hover-effect" /> -->

       <!--  <el-tooltip content="Global Size" effect="dark" placement="bottom">
          <size-select id="size-select" class="right-menu-item hover-effect" />
        </el-tooltip> -->

      </template>
      

      <el-dropdown class="avatar-container right-menu-item hover-effect" trigger="click">
        <div class="avatar-wrapper">
          <img :src="avatar" class="user-avatar">
        </div>
        <el-dropdown-menu slot="dropdown">
          <el-dropdown-item >
            <span style="display:block;" >Hi, <b>{{name}}</b></span>
          </el-dropdown-item>
          <router-link to="/" >
            <el-dropdown-item>Dashboard</el-dropdown-item>
          </router-link>
          <router-link v-if="roles.includes('user')" to="/my/profile">
            <el-dropdown-item>Profile</el-dropdown-item>
          </router-link>
          <a v-if="roles.includes('superadmin')" :href="telescopeURL" target="_blank">
            <el-dropdown-item>Monitoring</el-dropdown-item>
          </a>
          <router-link v-if="roles.includes('user')" to="/wallet/wallet">
            <el-dropdown-item>Wallet</el-dropdown-item>
          </router-link>
          <a href="#" @click="showChangePassword()">
            <el-dropdown-item>Change Password</el-dropdown-item>
          </a>           
          <el-dropdown-item divided>
            <span style="display:block;" @click="logout">Log Out</span>
          </el-dropdown-item>
        </el-dropdown-menu>
      </el-dropdown>
    </div>
  </div>
</template>

<script>
import { mapGetters } from 'vuex';
import Breadcrumb from '@/components/Breadcrumb';
import Hamburger from '@/components/Hamburger';
import Screenfull from '@/components/Screenfull';
import SizeSelect from '@/components/SizeSelect';
import LangSelect from '@/components/LangSelect';
import Search from '@/components/HeaderSearch';
import { getMyCartCount } from '@/api/user/shopping';
import { getToken } from '@/utils/auth';

export default {
  components: {
    Breadcrumb,
    Hamburger,
    Screenfull,
    SizeSelect,
    LangSelect,
    Search,
  },
  computed: {
    ...mapGetters([
      'sidebar',
      'name',
      'avatar',
      'device',
      'userId',
      'roles',
    ]),
  },
  data() {
    return {
      cartCount: 0,
      telescopeURL: '',
    };
  },
  created(){
    if (this.roles.includes('user')){
      this.updateCartCount();
    }
    var protocol = location.protocol;
    var slashes = protocol.concat('//');
    var host = slashes.concat(window.location.hostname);
    this.telescopeURL = host + '/telescope?token=' + getToken();
  },
  mounted() {
    this.$events.$on('update-cart-count', () => this.updateCartCount());
  },
  methods: {
    toggleSideBar() {
      this.$store.dispatch('app/toggleSideBar');
      this.$events.fire('menu-toggled-change');  
    },
    showChangePassword(){
      this.$events.fire('show-change-password');
    },

    updateCartCount(){
      getMyCartCount().then(response => {
        this.cartCount = response.data;
      });
    },
    async logout() {
      await this.$store.dispatch('user/logout');
      this.$router.push(`/login?redirect=${this.$route.fullPath}`);
    },
  },
};
</script>

<style lang="scss" scoped>

@media only screen and (max-device-width: 480px) {
    .app-breadcrumb.el-breadcrumb {
       display:none;
    }
}

.cart-btn{
  width: 40px;
  display: inline-block;
    cursor: pointer;
    color: #fcfdff;
    vertical-align: 12px;
}


.navbar .right-menu .right-menu-item{
    color: #fcfdff !important;
}

.navbar {
  height: 50px;
  overflow: hidden;
  position: relative;
  background: #2b2f3a;
  box-shadow: 0 1px 4px rgba(0,21,41,.08);

  .hamburger-container {
    line-height: 46px;
    height: 100%;
    float: left;
    cursor: pointer;
    transition: background .3s;
    -webkit-tap-highlight-color:transparent;

    &:hover {
      background: rgba(0, 0, 0, .025)
    }
  }

  .breadcrumb-container {
    float: left;
  }

  .errLog-container {
    display: inline-block;
    vertical-align: top;
  }

  .right-menu {
    float: right;
    height: 100%;
    line-height: 50px;

    &:focus {
      outline: none;
    }

    .right-menu-item {
      display: inline-block;
      padding: 0 8px;
      height: 100%;
      font-size: 18px;
      color: #5a5e66;
      vertical-align: text-bottom;

      &.hover-effect {
        cursor: pointer;
        transition: background .3s;

        &:hover {
          background: rgba(0, 0, 0, .025)
        }
      }
    }

    .avatar-container {
      margin-right: 30px;

      .avatar-wrapper {
        margin-top: 5px;
        position: relative;

        .user-avatar {
          cursor: pointer;
          width: 40px;
          height: 40px;
          border-radius: 20px;
        }

        .el-icon-caret-bottom {
          cursor: pointer;
          position: absolute;
          right: -20px;
          top: 25px;
          font-size: 12px;
        }
      }
    }
  }
}
</style>
