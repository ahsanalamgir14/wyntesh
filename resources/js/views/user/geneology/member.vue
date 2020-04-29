<template>
  <div class="app-container">
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
import { getAdminGeneology,getMyMemberGeneology } from "@/api/members";
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
    getGeneology(id){
      getMyMemberGeneology(id).then(response => {
        this.members = response.data;
      }).catch((err)=>{
        this.$router.back();
      });  
    }    
  }
};
</script>

<style scoped>
/*----------------geneology-scroll----------*/

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
