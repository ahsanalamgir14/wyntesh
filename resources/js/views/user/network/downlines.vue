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
      <el-select v-model="listQuery.is_active" style="width: 140px" placeholder="Status" class="filter-item" @change="handleFilter">
        <el-option  key="1200" label="All" value="all" />
        <el-option  key="1201" label="Active" value="1" />
        <el-option  key="1202" label="Inactive" value="0" />
      </el-select>

      <el-select v-model="listQuery.position" clearable style="width: 140px" placeholder="Leg" class="filter-item" @change="handleFilter">
        <el-option  key="1202" label="Leg A" value="1" />
        <el-option  key="1203" label="Leg B" value="2" />
        <el-option  key="1204" label="Leg C" value="3" />
        <el-option  key="1205" label="Leg D" value="4" />
      </el-select>

      <el-date-picker
        v-model="listQuery.date_range"
        class="filter-item"
        type="daterange"
        align="right"
        unlink-panels
        @change="handleFilter"
        format="yyyy-MM-dd"
        value-format="yyyy-MM-dd"
        range-separator="|"
        start-placeholder="Start date"
        end-placeholder="End date"
        :picker-options="pickerOptions">
      </el-date-picker>
      <el-button
        v-waves
        class="filter-item"
        type="primary"
        icon="el-icon-search"
        @click="handleFilter"
      >Search</el-button>
      <el-button
        v-waves
        :loading="downloadLoading"
        class="filter-item"
        type="primary"
        icon="el-icon-download"
        @click="handleDownload"
      >Export</el-button>
      
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
     
     <!--  <el-table-column
        label="Sr.No"
        prop="id"
        sortable="custom"
        align="center"
        width="80"
        :class-name="getSortClass('id')"
      >
        <template slot-scope="{row}">
          <span>{{ row.id }}</span>
        </template>
      </el-table-column> -->
      <el-table-column label="ID" width="150px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.user.username }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Name" width="250px" >
        <template slot-scope="{row}">
          <span >{{ row.user.name }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Rank" width="210px" align="center">
        <template slot-scope="{row}">
          <el-tag type="warning">{{ row.rank?row.rank.name:''}}</el-tag>
        </template>
      </el-table-column>
      <el-table-column label="Parent" width="210px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.parent.user.username}}</span>
        </template>
      </el-table-column>
      <el-table-column label="DOJ" width="210px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.created_at | parseTime('{y}-{m}-{d}') }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Personal BV" width="220px" align="right">
        <template slot-scope="{row}">
          <span>{{ row.total_personal_pv }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Group BV" width="200px" align="right">
        <template slot-scope="{row}">
          <span>{{ row.group_pv }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Status" class-name="status-col" width="145">
        <template slot-scope="{row}">
          <el-tag :type="row.user.is_active | statusFilter">{{ row.user.is_active?'Active':'Deactive' }}</el-tag>
        </template>
      </el-table-column>
    </el-table>

    <pagination
      v-show="total>0"
      :total="total"
      :page.sync="listQuery.page"
      :limit.sync="listQuery.limit"
      @pagination="getList"
    />

  </div>
</template>

<script>
import { getDownlines } from "@/api/user/members";
import waves from "@/directive/waves"; 
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination";

export default {
  name: "downlines",
  components: { Pagination },
  directives: { waves },
  filters: {
    statusFilter(status) {
      const statusMap = {
        1: "success",
        null: "info",
        0: "danger"
      };

      return statusMap[status];
    }
  },
  data() {
    return {
      tableKey: 0,
      list: null,
      total: 0,
      listLoading: true,
      listQuery: {
        page: 1,
        limit: 10,
        is_active:'all',
        sort: "-id",
        date_range:''
      },
      sortOptions: [
        { label: "ID Ascending", key: "+id" },
        { label: "ID Descending", key: "-id" }
      ],
      downloadLoading: false,
      buttonLoading: false,
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
    };
  },
  created() {
    this.getList();
  },
  methods: {
    getList() {
      this.listLoading = true;
      
      getDownlines(this.listQuery).then(response => {
        this.list = response.data.data;
        this.total = response.data.total;
        setTimeout(() => {
          this.listLoading = false;
        }, 1 * 100);
      }).catch((er)=>{
        this.listLoading = false;
      });
    },        
    resetTemp() {
      this.temp = {
        id: undefined,
        debit:0,
        tds_amount:0,
        final_debit:0,
      };
    },
    clean(obj) {
      for (var propName in obj) { 
        if (obj[propName] === null || obj[propName] === undefined) {
          delete obj[propName];
        }
      }
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
    },
    handleDownload() {
      this.downloadLoading = true;
      import("@/vendor/Export2Excel").then(excel => {
        const tHeader = [
          "ID",
          "Name",
          "Parent",
          "DOJ",          
          "Status"
        ];
        const filterVal = [
          "id",
          "name",
          "parent",
          "doj",
          "status"
        ];
        const data = this.formatJson(filterVal, this.list);
        excel.export_json_to_excel({
          header: tHeader,
          data,
          filename: "withdrawals"
        });
        this.downloadLoading = false;
      });
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v =>
        filterVal.map(j => {
          if (j === "doj") {
            return parseTime(v.created_at);
          }else if(j=='id') {
            return v.user.username
          }else if(j=='name') {
            return v.user.name
          }else if(j=='parent') {
            return v.parent.user.username
          }else if(j=='status') {
            return v.user.is_active?'Active':'Inactive'
          }
          else {
            return v[j];
          }
        })
      );
    },
    
  }
};
</script>

<style lang="scss" scoped>

.pagination-container {
  margin-top: 5px;
}
.pagination-container {
  background: #fff;
  padding: 15px 16px;
}

</style>
