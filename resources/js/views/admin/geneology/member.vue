<template>
  <div class="app-container">
    <el-row style="margin: 30px 20px 0px 20px;">
      <el-col  :xs="6" :sm="2" :md="2" :lg="2" :xl="2" >
          <div class="about-images">
            <img height="50px" width="50px" src="@/assets/images/active.png" alt="Member">
            <h6>Active Member</h6>
          </div>
      </el-col>
      <el-col  :xs="6" :sm="2" :md="2" :lg="2" :xl="2" >
          <div class="about-images">
            <img height="50px" width="50px" src="@/assets/images/kyc-pending.png" alt="Member">
            <h6>Kyc Pending</h6>
          </div>
      </el-col>
      <el-col  :xs="6" :sm="2" :md="2" :lg="2" :xl="2" >
          <div class="about-images">
            <img height="50px" width="50px" src="@/assets/images/deactive.png" alt="Member">
            <h6>Deactive Member</h6>
          </div>
      </el-col>
      <el-col  :xs="6" :sm="2" :md="2" :lg="2" :xl="2" >
          <div class="about-images">
            <img height="50px" width="50px" src="@/assets/images/add.png" alt="Member">
            <h6>Add Member</h6>
          </div>
      </el-col>
      <el-col  :xs="24" :sm="16" :md="16" :lg="16" :xl="16" >
        <div class="filter-container" style="float: right;margin-top: 10px;">
          <el-input
            v-model="memberId"
            placeholder="Enter member ID"
            style="width: 200px;"
            class="filter-item"
            @keyup.enter.native="viewMemberTree"
          />
          <el-button
            v-waves
            class="filter-item"
            type="primary"
            icon="el-icon-search"
            @click="viewMemberTree"
          >View</el-button>
        </div>
      </el-col>
    </el-row>
    <div class="body geneology-body geneology-scroll">
      <div class="geneology-tree">
        <ul>
          
          <node :node="members"></node>
          
        </ul>
      </div>
    </div>
  </div>
</template>

<script>
import { getAdminGeneology,getAdminMemberGeneology } from "@/api/members";
import waves from "@/directive/waves"; // waves directive
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination"; // secondary package based on el-pagination
import axios from "axios";
import Tinymce from '@/components/Tinymce'
import userImage from '@/assets/images/tree-user.png';
import node from "./components/node";

export default {
  name: "ComplexTable",
  components: { Pagination,Tinymce,node },
  directives: { waves },
  filters: {
    statusFilter(status) {
      const statusMap = {
        1: "success",
        draft: "info",
        0: "danger"
      };

      return statusMap[status];
    }
  },
  data() {
    return {   
      memberId:undefined,
      members:{
        id:0,
        user:{
          username:undefined,
          name:undefined
        }        
      },   
      temp: {
        title: undefined
      },
    };
  },
  created() {
    //this.members.id=this.$route.params.id;
    this.getGeneology(this.$route.params.id);
  },
  methods: {
    viewMemberTree(){
      if(this.memberId){
        this.$router.push('/geneology/member/'+this.memberId);  
      }      
    },
    getGeneology(id){
      getAdminMemberGeneology(id).then(response => {
        this.members = response.data;
      }).catch((err)=>{
        this.$router.back();
      });  
    }    
  }
};
</script>

<style scoped lang="scss">
/*----------------geneology-scroll----------*/
.about-images{
  width:50px;
  text-align: center;
  img{
    height:30px;
    width:30px;
  }
  h6{
    margin-top:0px;
    margin-bottom:0px;
  }
}
.app-container{
  padding-top: 0px;
}
.geneology-scroll::-webkit-scrollbar {
    width: 5px;
    height: 8px;
}
.geneology-scroll::-webkit-scrollbar-track {
    border-radius: 10px;
    background-color: #e4e4e4;
}
.geneology-scroll::-webkit-scrollbar-thumb {
    background: #212121;
    border-radius: 10px;
    transition: 0.5s;
}
.geneology-scroll::-webkit-scrollbar-thumb:hover {
    background: #d5b14c;
    transition: 0.5s;
}


/*----------------geneology-tree----------*/
.geneology-body{
    white-space: nowrap;
    overflow-y: hidden;
    padding: 50px;
    min-height: 500px;
    padding-top: 10px;
}
.geneology-tree ul {
    padding-top: 20px; 
    position: relative;
    padding-left: 0px;
    display: flex;
}
.geneology-tree ul ul::before{
    content: '';
    position: absolute; top: 0; left: 50%;
    border-left: 2px solid #ccc;
    width: 0; height: 20px;
}
</style>
