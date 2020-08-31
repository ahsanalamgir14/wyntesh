<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input
        v-model="listQuery.search"
        placeholder="Search Records"
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
        <ul class="parent">        
            <li v-for="data in list" class="child">
                <div class='logo'>{{data.id}}
                </div>
                <div class='image'>
                    <img :src="data.profile_picture?data.profile_picture:avatar" width="50" height="50" class="imagecontent">
                </div>
                <div class="name">{{data.name}}<br>{{data.username}}</div>
                <div class="age">Age:&nbsp;{{data.age}}</div>
                <div class="city">{{data.city}}</div>
                <div class="amount">{{data.total_amount}}</div>
            </li>
        </ul>
    </div>

   <!--  <el-table
      :key="tableKey"
      v-loading="listLoading"
      :data="list"
      border
      fit
      highlight-current-row
      style="width: 100%;"
      @sort-change="sortChange"
    >
     

      <el-table-column label="Rank" min-width="60px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.id }}</span>
        </template>
      </el-table-column>

      <el-table-column label="Name" width="140px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.name }}</span>
        </template>
      </el-table-column>

      <el-table-column label="ID" width="140px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.username }}</span>
        </template>
      </el-table-column>

      <el-table-column label="Income" width="100px" align="center">
        <template slot-scope="{row}">
          <span >{{ row.total_amount }}</span>
        </template>
      </el-table-column>
     
      <el-table-column label="Age" width="50px" align="center">
        <template slot-scope="{row}">
          <span >{{ row.age }}</span>
        </template>
      </el-table-column>

      <el-table-column label="City" min-width="130px" align="center">
        <template slot-scope="{row}">
          <span >{{ row.city }}</span>
        </template>
      </el-table-column>
  = <el-table-column label="Payable" width="130px" align="right">
        <template slot-scope="{row}">
          <span >{{ row.total_amt }}</span>
        </template>
      </el-table-column> 
    </el-table> -->

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


.parent .child{
    display: flex;
    border: solid 2px #5a4b9c;
    border-radius: 28px;
    padding: 1px;
    width: 48%;
    margin-bottom: 7px;
}
.parent .child .image{
    margin: 5px 21px;
    border-radius: 11px;
}
.parent .child .imagecontent{
    border-radius: 16px;
        margin-top: 15px;

}
.parent .child .logo{
   margin-top: 19px !important;
    margin-left: 7px;
    border: solid 2px #fbc0c0;
    background-color: #d45252;
    width: 10%;
    text-align: center;
    font-weight: 600;
    height: 0px;
    border-radius: 29px;
    padding: 14px 9px 32px;
    text-align: center;
    color: white;
    font-size: 21px;


}
.parent .child .logocontent{
    border-radius: 16px;
    margin-top: 38px;
}
.parent .child .name{
      text-align: left;
    font-weight: 600;
    padding: 5px;
    font-family: sans-serif;
    width: 40%;
    min-width: 23%;
    color: #615050;
    margin: 8px 9px;
    font-size: 21px;
}
.parent .child .index{
  

}
.parent .child .amount{
    margin: 24px 28px;
    font-size: 22px;
    font-weight: 600;
    color: #615050;
    margin-top: 34px;
}

.parent .child .city{
    margin: 12px;
    border-radius: 13px;
    padding: 11px;
    font-weight: 600;    
    max-width: 50%;
    width: 27%;
    color: #615050;
    font-size: 20px;
    margin-top: 24px;
}

.parent .child .age{
    margin: 15px 7px;
/*    background-color: #a4acb3;
    border-radius: 13px;*/
    padding: 12px;
    font-weight: 600;
    color: #0c0a0a;
    /*border: solid 2px #111111;*/
    width: 20%;

}
@media (min-width:450px) {
    .parent .child .logo{
        margin-top: -13px;
    }
}

@media (max-width:450px) {
   
    .parent .child{
         display: flex;
        border: solid 2px #5a4b9c;
        border-radius: 28px;
        padding: 1px;
        width: 118%;
        margin-bottom: 7px;
        margin: 10px -51px;
        height: 68px;
    }
    .parent .child .image{
        position: relative;
        margin: 4px 4px;
        height: 2px;

    }
    .parent .child .imagecontent{
        border-radius: 16px;
        width: 29px;
        height: 31px;
        margin-top: 7px;
    }
    .parent .child .logo{
        margin: 5px 4px;
        margin-top: 4px !important;
        text-align: center;
        font-size: 15px;
        font-size: 14px;
        right: left;
    
    }
    .parent .child .logocontent{
        border-radius: 16px;
        margin-top: 18px;
    }
    .parent .child .name{
        text-align: left;
        font-weight: 600;
        padding: 5px;
        font-family: sans-serif;
        width: 40%;
        max-width: 100%;
        color: #615050;
        margin: 8px 5px;
        font-size: 12px;
    }
    .parent .child .index{
        position: absolute;
        left: 19px;
        top: 30px;
        font-weight: bold;
        font-size: 10px;
        color: #333333;


    }
    .parent .child .amount{
        margin: 23px 28px;
        font-size: 13px;
        font-weight: 600;
        color: #615050;
    }
    .parent .child .age{
        margin: 0px 20px;
        padding: 12px;
        font-weight: 600;
        color: #040303;
        background-color: transparent;
        border: none;
        width: 20%;
        font-size: 14px;
        min-width: 32px;

    }
    .parent .child .city{
        margin: 0px;
        border-radius: 13px;
        padding: 11px;
        font-weight: 600;
        min-width: 14%;
        width: 27%;
        color: #615050;
        font-size: 10px;
        margin-top: 9px;
        margin-left: -10px;
    }
}


</style>
