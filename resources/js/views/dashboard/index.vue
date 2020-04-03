<template>
  <div class="dashboard-container">
    <component :is="currentRole" />
  </div>
</template>

<script>
import { mapGetters } from 'vuex';
import adminDashboard from './admin';
import superAdminDashboard from './superadmin';
import franchiseDashboard from './franchise';
import editorDashboard from './editor';

export default {
  name: 'Dashboard',
  components: { adminDashboard, editorDashboard, superAdminDashboard,franchiseDashboard},
  data() {
    return {
      currentRole: 'adminDashboard',
    };
  },
  computed: {
    ...mapGetters([
      'roles',
    ]),
  },
  created() {
    if (this.roles.includes('admin')) {
      this.currentRole = 'adminDashboard';
    }else if (this.roles.includes('franchise')) {
      this.currentRole = 'franchiseDashboard';
    }
    else if (this.roles.includes('superadmin')) {
      this.currentRole = 'superAdminDashboard';
    }
  },
};
</script>
