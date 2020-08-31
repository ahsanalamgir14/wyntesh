<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input
        v-model="listQuery.search"
        placeholder="Member Id"
        style="width: 200px;"
        class="filter-item"
        @keyup.enter.native="handleFilter"
      />
      <el-button
        v-waves
        class="filter-item"
        type="primary"
        icon="el-icon-search"
        @click="handleFilter"
      >Search</el-button> 
    </div>

   
    <div class="row">
        <h1 style="margin: 21px;">Wall of wyntash</h1> 
        <ul class="parent">        
              <div class="card-wrapper" v-for="data in list">
                <div class="card-container">
                  <div class="img-container">
                    <img :src="data.profile_picture?data.profile_picture:avatar"  alt="">
                  </div>
                  
                  <div class="content">
                    <div class="head">
                        <p>{{data.name}}</p>
                        <div class="age">
                          <span>Age :</span> <span>{{data.age}}</span>
                        </div>
                        <div class="memberid">
                          <span>Id :</span> <span>{{data.username}}</span>
                        </div>
                        <div class="city">
                          <span>City :</span> <span>{{data.city}}</span>
                        </div>
                    </div>
                    <div class="data-1">
                        <p> <span>&#8377;</span> {{data.total_amount}}</p>
                    </div>
                  </div>
                  
                  <div class="floating-icon">
                    <span>{{data.id}}</span>
                  </div>
                </div>
              </div>



        </ul>
    </div>


      <div :class="{'hidden':hidden}" class="pagination-container">
            <el-pagination
              :current-page.sync="listQuery.page"
              :page-size.sync="pageSize"
              :layout="layout"
              :page-sizes="pageSizes"
              :total="this.total_data"

              @size-change="handleSizeChange"
              @current-change="handleCurrentChange"
            />
          </div>

  </div>
</template>

<script>
import { fetchAllEliteMember, } from "@/api/user/payouts";
import avatar from '@/assets/images/avatar.png'
import waves from "@/directive/waves"; // waves directive
import { parseTime } from "@/utils";
// import Pagination from "@/components/Pagination"; 
import { scrollTo } from '@/utils/scroll-to';

export default {
  name: "Payouts",
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
      tableKey: 0,
      avatar: avatar,
      hidden: false,
      pageSize: 10,
      layout: 'total, sizes, prev,next, jumper',
      pageSizes: [5,10, 15, 20, 30, 50,500,5000],
      list: null,
      total_data: 0,
      listLoading: true,
      listQuery: {
        page: 1,
        limit: 10,
        search: undefined,
        sort: "+id"
      },
      sortOptions: [
        { label: "ID Ascending", key: "+id" },
        { label: "ID Descending", key: "-id" }
      ],
      pickerOptions: {
        shortcuts: [{
          text: 'Last week',
          onClick(picker) {
            const end = new Date();
            const start = new Date();
            start.setTime(start.getTime() - 3600 * 1000 * 24 * 7);
            picker.$emit('pick', [start, end]);
          }
        }, {
          text: 'Last month',
          onClick(picker) {
            const end = new Date();
            const start = new Date();
            start.setTime(start.getTime() - 3600 * 1000 * 24 * 30);
            picker.$emit('pick', [start, end]);
          }
        }, {
          text: 'Last 3 months',
          onClick(picker) {
            const end = new Date();
            const start = new Date();
            start.setTime(start.getTime() - 3600 * 1000 * 24 * 90);
            picker.$emit('pick', [start, end]);
          }
        }]
      },
      dialogPayoutGenerateVisible:false,
      dialogStatus: "",
      dialogTitle:"",
      textMap: {
        update: "Edit",
        create: "Create"
      },
      downloadLoading: false,
      buttonLoading: false
    };
  },
  created() {
    this.getList();
  },
  methods: {
    getList() {
      this.listLoading = true;
      fetchAllEliteMember(this.listQuery).then(response => {
        console.log(response.data.data);
        this.list = response.data.data;
        this.total_data = response.data.total;
        setTimeout(() => {
          this.listLoading = false;
        }, 1 * 100);
      });
    },

    handleSizeChange(val) {
      
    },
    handleCurrentChange(val) {
        this.getList();
    },


    handleFilter() {
      this.listQuery.page = 1;
      this.getList();
    },
    sortChange(data) {
      const { prop, order } = data;
      if (prop === "id") {
        this.sortByID(order);
      }
    },
    sortByID(order) {
      if (order === "ascending") {
        this.listQuery.sort = "+id";
      } else {
        this.listQuery.sort = "-id";
      }
      this.handleFilter();
    },
    getSortClass: function(key) {
      const sort = this.listQuery.sort;
      return sort === `+${key}`
        ? "ascending"
        : sort === `-${key}`
        ? "descending"
        : "";
    }
  }
};
</script>

<style scoped>
.parent{
    padding: 0;
}

.el-drawer__body {
  padding: 20px;
}
.pagination-container {
  margin-top: 5px;
}
.pagination-container {
  background: #fff;
  padding: 15px 16px;
}

body{
  background: #dedfe1;
  font-family: 'Open Sans', sans-serif;
}
.card-wrapper{
  position: relative;
  padding: 20px;
}

.card-container{
  position: relative;
  width: 580px;
  height: 200px;
  background: #fff;
  border-radius: 5px;
  box-shadow: 0 0 10px rgba(0,0,0,0.5);
  background-color: antiquewhite;
}

.img-container{
  width: 36%;
  height: 100%;
  float: left;
  position: relative;
}

.img-container img{
  width: 100%;
  height: 100%;
  border-top-left-radius: 5px;
  border-bottom-left-radius: 5px;
}

.content{
  width: 60%;
  height: 100%;
  float: left;
  padding: 30px 40px;
  box-sizing: border-box;
}

.head{
  padding-bottom: 30px;
}

.head p{
  font-size: 28px;
  color: #444444;
  font-weight: 600;
  margin: 0;
}

.head span{
  color: #aaabaf;
  font-size: 14px;
}

.data{
  width: 90%; 
  overflow: hidden;
}

.inner-data{
  width: 50%;
  float: left;
  text-align: left;
  color: #aaabaf;
}

.inner-data p{
  font-size: 14px;
  padding-bottom: 5px;
}

.inner-data span{
  font-size: 18px;
  font-weight: 400;
}

.floating-icon{
  position: absolute;
    width: 70px;
    height: 70px;
    top: 38%;
    right: -7%;
    background: #1DA1F2;
    border-radius: 50%;
    cursor: pointer;
}

.floating-icon span{
  padding-top: 25px;
    text-align: center;
    /* margin-left: auto; */
    display: block;
    font-weight: 600;
    color: #fff;
}

.btn{
  position: absolute;
  top: 10px;
  right: 10px;
  text-transform: uppercase;
  font-size: 12px;
  background: #1DA1F2;
  padding: 6px 15px;
  border-radius: 50px;
  color: #fff;
  font-weight: 600;
  opacity: 0;
  transition: all 1s ease;
  cursor: pointer;
}

.age span,
.city span{
  font-weight: 600;
}
.age span,
.memberid span{
  font-weight: 600;
}

.data-1 p{
    font-size: 28px;
    color: #444444;
    font-weight: 600;
    color: #1DA1F2;
    margin: 0;
}

.btn.active{
  opacity: 1;
}

@media (max-width:450px) {
    .card-wrapper{
        width: 100%;
    }

    .card-container[data-v-33d760b4]{
        width: 100%;
        height: 500px;
        background-color: antiquewhite;
    }
    .img-container[data-v-33d760b4]{
        width: 100%;
        height: auto;
    }
    .img-container[data-v-33d760b4] img{
        width: calc(100% - 60px);
        margin-left: auto;
        height: auto;
        max-height: 270px;
        display: block;
        margin-right: auto;
    }
    .content[data-v-33d760b4] {
    width: 100%;
    height: calc(100% - 275px - 60px);
    /*float: left;*/
    padding: 30px 40px;
    box-sizing: border-box;
}
}

</style>
