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
            <!-- <img :src="bgImage" class="background-image"> -->

        <ul class="parent">        
            <div class="card-wrapper" v-for="(data,index) in list">

                <div class="card-container"  :class="[data.id==1 ? 'first' : '', data.id==2 ? 'second' : '', data.id==3 ? 'second' : '', data.id==4 ? 'second' : '', data.id==5 ? 'second' : '']">
                    <div class="img-container">
                        <img :src="data.user.profile_picture?data.user.profile_picture:avatar"  alt="">
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
                      <div v-if="data.id==1" >
                        <div v-if="is_mobile">
                          <img :src="crown" class="crown"/>
                        </div>
                        <div v-else>
                          <img :src="crown" class="crown"/>
                        </div>
                      </div>
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
import bgImage from '@/assets/images/wall-of-wyntash.jpeg'
import crown from '@/assets/images/crown.png'
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
      is_mobile:false,
      tableKey: 0,
      avatar: avatar,
      bgImage: bgImage,
      crown: crown,
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
    if(window.screen.width <= '550'){
      this.is_mobile=true;
    }
  },
  methods: {
    getList() {
      this.listLoading = true;
      fetchAllEliteMember(this.listQuery).then(response => {
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

<style scoped type="css">
@import url('https://fonts.googleapis.com/css2?family=Courgette&display=swap');
.background-image{
    width: 580px;
    height: 10%;
    margin-left: 23px;
}
.heading{
  color: #68bd3e;
  content: unset;
  font-size: 53px;
  text-transform: capitalize;
  text-decoration: underline;
  margin-left: 88px;
}
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

.card-wrapper{
  position: relative;
  padding: 20px;
}

.card-container{
  box-shadow: rgba(0, 0, 0, 0.25) 0px 2px 20px, rgba(0, 0, 0, 0.22) 0px 0px 8px;
  position: relative;
  width: 580px;
  height: 200px;
  background: #fff;
  border-radius: 5px;
  background-color: silver;
  font-family: inherit;
}

.first{
  background: rgb(255,215,0);
  background: linear-gradient(90deg, rgba(255,215,0,1) 28%, rgba(230,182,36,1) 61%);
  color: white !important;
  font-family: inherit;
}

.second{
  background: rgb(255,215,0);
  background: linear-gradient(90deg, rgba(255,215,0,1) 28%, rgba(230,182,36,1) 61%);
  color: white !important;
  font-family: inherit;
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
  font-family: inherit;
}

.first .head p{
   color: white !important;
}

.second .head p{
   color: white !important;
}



.first span{
  color: white;
}

.second span{
  color: white;
}


.head span{
  color: #67696f;
  font-size: 14px;
}
.first span{
  color: white;
  font-size: 14px;
}
.second span{
  color: white;
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
    width: 100px;
    height: 100px;
    top: 25%;
    right: -7%;
    background: #ea4848;
    border-radius: 50%;
    cursor: pointer;
}

.floating-icon span{
  padding-top: 29px;
    text-align: center;
    /* margin-left: auto; */
    display: block;
    font-weight: 600;
    color: #fff;
    font-size:41px;
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

.first .data-1 p, .second .data-1 p{
    color: white;
}
.btn.active{
  opacity: 1;
}

.crown{
    width: 50px;
  position: absolute !important;
  top: 3px !important;
  left: 25px !important;
  color: #e6b742 !important;
}
@media (max-width:450px) {
  
  .crown{
    width: 35px;
    position: absolute !important;
    top: 2px !important; 
    left: 20px !important;
    color: #e6b742 !important;
  }
    .background-image{
            width: 100%;
            margin-left: 23px !important;
            padding-right: 45px;
            height: 100%;
    }
    .floating-icon span{
        padding-top: 19px;
        text-align: center;
        display: block;
        font-weight: 600;
        color: #fff;
        font-size: 38px;
    
    }
    .floating-icon{
            position: absolute;
            width: 75px;
            height: 75px;
            top: 36%;
            right: -10%;
            background: ea4848;
            border-radius: 50%;
            cursor: pointer;

    }


    .card-wrapper{
        width: 100%;
    }

    .card-container{
        width: 100%;
        height: 130px;
        background-color: silver;
    }
    .img-container{
        
        height: auto;
    }
    .img-container img{
        
        margin-left: auto;
        height: auto;
        max-height: 130px;
        display: block;
        margin-right: auto;
    }
    .content {
    
    height: calc(100% - 275px - 60px);
    /*float: left;*/
    padding: 10px 15px;
    box-sizing: border-box;
}
.head {
  padding-bottom: 10px;
  }
.head p{
  font-size: 16px;
  }

  .data-1 p{
    font-size: 16px;
}
}

</style>
