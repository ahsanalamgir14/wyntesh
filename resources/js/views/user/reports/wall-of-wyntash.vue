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

    <el-table
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
     <!--  <el-table-column label="Payable" width="130px" align="right">
        <template slot-scope="{row}">
          <span >{{ row.total_amt }}</span>
        </template>
      </el-table-column> -->
    </el-table>

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
        console.log(response);
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

</style>
