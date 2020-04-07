<template>
  <div :class="classObj" class="app-wrapper">
    <div v-if="device==='mobile'&&sidebar.opened" class="drawer-bg" @click="handleClickOutside" />
    <sidebar class="sidebar-container" />
    <div :class="{hasTagsView:needTagsView}" class="main-container">
      <div :class="{'fixed-header':fixedHeader}">
        <navbar />
      </div>
      <app-main />
      <right-panel v-if="showSettings">
        <settings />
      </right-panel>
    </div>
    <el-dialog title="Change Password" width="300px" :visible.sync="dialogChangePassword">
      <el-row>
        
              <el-form ref="dataForm" :rules="rules" :model="temp">
                <el-col  :xs="24" :sm="24" :md="24" :lg="24" :xl="24" >
                  <el-form-item label="New Password" prop="password">
                    <el-input type="password" v-model="temp.password" />
                  </el-form-item>
                </el-col>

              </el-form>
        </el-row>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogChangePassword = false">
          Cancel
        </el-button>
        <el-button type="primary" @click="handleChangePassword()">
          Change Password
        </el-button>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import RightPanel from '@/components/RightPanel';
import { Navbar, Sidebar, AppMain, TagsView, Settings } from './components';
import ResizeMixin from './mixin/resize-handler.js';
import { mapState } from 'vuex';
import { changePassword } from '@/api/auth'

export default {
  name: 'Layout',
  components: {
    AppMain,
    Navbar,
    RightPanel,
    Settings,
    Sidebar,
    TagsView,
  },
  mixins: [ResizeMixin],
  computed: {
    ...mapState({
      sidebar: state => state.app.sidebar,
      device: state => state.app.device,
      showSettings: state => state.settings.showSettings,
      needTagsView: state => state.settings.tagsView,
      fixedHeader: state => state.settings.fixedHeader,
    }),
    classObj() {
      return {
        hideSidebar: !this.sidebar.opened,
        openSidebar: this.sidebar.opened,
        withoutAnimation: this.sidebar.withoutAnimation,
        mobile: this.device === 'mobile',
      };
    },
  },
  mounted() {
    this.$events.$on("show-change-password", () => this.showChangePasswordDialogue());
  },
  data() {
    return {
       dialogChangePassword:false,
      temp: {
        password:''
      },
      rules: {
        password: [{ required: true, message: 'Password is required', trigger: 'blur' }]
      }
    }
  },
  methods: {
    handleChangePassword(){
       this.$refs["dataForm"].validate(valid => {
        if (valid) {
          changePassword(this.temp).then((data) => {
            this.dialogChangePassword = false;
            this.$notify({
              title: "Success",
              message: "Password changed Successfully",
              type: "success",
              duration: 2000
            });
          });
        }
      });
    },
    showChangePasswordDialogue(){
      this.dialogChangePassword=true;
    },
    handleClickOutside() {
      this.$store.dispatch('app/closeSideBar', { withoutAnimation: false });
    },
  },
};
</script>

<style lang="scss" scoped>
  @import "~@/styles/mixin.scss";
  @import "~@/styles/variables.scss";

  .app-wrapper {
    @include clearfix;
    position: relative;
    height: 100%;
    width: 100%;

    &.mobile.openSidebar {
      position: fixed;
      top: 0;
    }
  }

  .drawer-bg {
    background: #000;
    opacity: 0.3;
    width: 100%;
    top: 0;
    height: 100%;
    position: absolute;
    z-index: 999;
  }

  .fixed-header {
    position: fixed;
    top: 0;
    right: 0;
    z-index: 9;
    width: calc(100% - #{$sideBarWidth});
    transition: width 0.28s;
  }

  .hideSidebar .fixed-header {
    width: calc(100% - 54px)
  }

  .mobile .fixed-header {
    width: 100%;
  }
</style>
