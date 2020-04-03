<template>
  <div class="app-container">
    <el-form  :model="user">
      <el-row :gutter="20">
        <el-col :span="6">
          <user-card :user="user" />
        </el-col>
        <el-col :span="18">
          <user-activity :user="user" />
        </el-col>
      </el-row>
    </el-form>
  </div>
</template>

<script>
import {getFranchiseProfile} from '@/api/franchises';
import UserBio from './components/UserBio';
import UserCard from './components/UserCard';
import UserActivity from './components/UserActivity';

//const userResource = new Resource('users');
export default {
  name: 'Profile',
  components: { UserBio, UserCard, UserActivity },
  data() {
    return {
      user: {
        id: undefined,
        name: undefined,
      }
    };
  },
  created() {
    getFranchiseProfile().then(response => {
        this.user = response.data
    });
  },
  methods: {
    
  },
};
</script>
