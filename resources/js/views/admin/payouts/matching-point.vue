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
      <el-button
        class="filter-item"
        style="margin-left: 10px;"
        type="success"
        icon="el-icon-refresh-left"
        @click="handleCreate"
      > Generate</el-button>
      <el-button
        v-waves
        :loading="downloadLoading"
        class="filter-item"
        type="warning"
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
      show-summary :summary-method="getSummaries"
    >
    
      <el-table-column
        label="ID"
        prop="id"
        sortable="custom"
        align="center"
        width="80"
        :class-name="getSortClass('id')"
      >
        <template slot-scope="{row}">
          <span>{{ row.id }}</span>
        </template>
      </el-table-column>
      
      <el-table-column label="Member ID" min-width="120px" align="center">
        <template slot-scope="{row}">
          <span >{{ row.member.user.username }}</span>
        </template>
      </el-table-column>

      <el-table-column label="Member Name" min-width="130px" align="center">
        <template slot-scope="{row}">
          <span >{{ row.member.user.name }}</span>
        </template>
      </el-table-column>

      <el-table-column label="LEG A" width="90px" align="center">
        <template slot-scope="{row}">
          <span >{{ row.leg_1 }}</span>
        </template>
      </el-table-column>
      <el-table-column label="LEG B" width="90px" align="center">
        <template slot-scope="{row}">
          <span >{{ row.leg_2 }}</span>
        </template>
      </el-table-column>
      <el-table-column label="LEG C" width="90px" align="center">
        <template slot-scope="{row}">
          <span >{{ row.leg_3 }}</span>
        </template>
      </el-table-column>
      <el-table-column label="LEG D" width="90px" align="center">
        <template slot-scope="{row}">
          <span >{{ row.leg_4 }}</span>
        </template>
      </el-table-column>
     <!--  <el-table-column label="Previous CF" width="130px" align="center">
        <template slot-scope="{row}">
          <span >{{ row.previous_carry_forward }}</span>
        </template>
      </el-table-column> -->
      <el-table-column label="Matched" width="100px" align="center">
        <template slot-scope="{row}">
          <span >{{ row.matched }}</span>
        </template>
      </el-table-column>
      <!-- <el-table-column label="Total Sales" width="100px" align="center">
        <template slot-scope="{row}">
          <span >{{ row.total_sales }}</span>
        </template>
      </el-table-column> -->
      <!-- <el-table-column label="Carry Forward" width="120px" align="center">
        <template slot-scope="{row}">
          <span >{{ row.carry_forward }}</span>
        </template>
      </el-table-column> -->
    </el-table>

    <pagination
      v-show="total>0"
      :total="total"
      :page.sync="listQuery.page"
      :limit.sync="listQuery.limit"
      @pagination="getList"
    />

    <el-dialog :title="dialogTitle" width="50%" top="30px"  :visible.sync="dialogGenerateMatchingVisible">
      <el-form ref="generateMatching" :rules="rules" :model="temp" style="">
        <el-row :gutter="20">
          
          <el-col  :xs="24" :sm="24" :md="24" :lg="24" :xl="24" >
            <el-form-item label="Sales date range" prop="date_range">
              <br>
              <div class="block">
                <el-date-picker
                style="width:100%"
                  v-model="temp.date_range"
                  type="daterange"
                  align="right"
                  unlink-panels
                  format="yyyy-MM-dd"
                  value-format="yyyy-MM-dd"
                  range-separator="To"
                  start-placeholder="Sale Start date"
                  end-placeholder="Sale End date">
                </el-date-picker>
              </div>
            </el-form-item>
          </el-col>

        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogGenerateMatchingVisible = false">
          Cancel
        </el-button>
        <el-button type="primary" icon="el-icon-finished" :loading="buttonLoading" @click="dialogStatus==='create'?createData():updateData()">
          Save
        </el-button>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import { getMatchingPoints, generateMatchingPoints,} from "@/api/admin/payouts";
import { getAllIncomes,} from "@/api/admin/incomes";
import waves from "@/directive/waves"; // waves directive
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination"; 
import role from '@/directive/role'; 
import checkRole from '@/utils/role';

export default {
  name: "GeneratePayout",
  components: { Pagination },
  directives: { waves,role },
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
      list: null,
      total: 0,
      listLoading: true,
      listQuery: {
        page: 1,
        limit: 10,
        search: undefined,
        sort: "-id"
      },
      sums:{
        total_matched:0,
        total_bv:0,  
      },
      sortOptions: [
        { label: "ID Ascending", key: "+id" },
        { label: "ID Descending", key: "-id" }
      ],
      temp: {
        date_range:undefined,
        incomes:undefined
      },
      income_list:[],
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
      dialogGenerateMatchingVisible:false,
      dialogStatus: "",
      dialogTitle:"",
      textMap: {
        update: "Edit",
        create: "Create"
      },
      rules: {
        date_range: [{ required: true, message: 'Date range is required', trigger: 'blur' }],
        incomes: [{ required: true, message: 'Please select income', trigger: 'blur' }],
      },
      downloadLoading: false,
      buttonLoading: false
    };
  },
  created() {
    this.getList();
    getAllIncomes().then(response => {
      this.income_list = response.data;
    });
  },
  methods: {
    checkRole,
    getList() {
      this.listLoading = true;
      getMatchingPoints(this.listQuery).then(response => {
        this.list = response.data.data;
        this.total = response.data.total;
        this.sums = response.sum;
        setTimeout(() => {
          this.listLoading = false;
        }, 1 * 100);
      });
    },
    getSummaries(param) {
      // alert(param)
      const { columns, data } = param;
      const sums = [];
      columns.forEach((column, index) => {
        if (index === 1) {
          sums[index] = 'Total Sales - '+this.sums.total_bv;
          return;
        }
        if (index === 2) {
          sums[index] = 'Total Matched - '+this.sums.total_matched;
          return;
        }
      });

      return sums;
    },
    handleFilter() {
      this.listQuery.page = 1;
      this.getList();
    },
    
    resetTemp() {
      this.temp = {
        id: undefined,
        date_range:undefined,
        incomes:undefined
      };
    },
    handleCreate() {
      this.resetTemp();
      this.dialogStatus = "create";
      this.dialogTitle="Generate Matching Point Report";
      this.dialogGenerateMatchingVisible = true;
      this.$nextTick(() => {
        this.$refs["generateMatching"].clearValidate();
      });
    },
    createData() {
      this.$refs["generateMatching"].validate(valid => {
        if (valid) {
          this.buttonLoading=true;
          generateMatchingPoints(this.temp).then((data) => {
            this.dialogGenerateMatchingVisible = false;
            this.getList();
            this.$notify({
              title: "Success",
              message: data.message,
              type: "success",
              duration: 2000
            });
            this.buttonLoading=false;
            this.resetTemp();
          }).catch((err)=>{
            this.buttonLoading=false;
          });
        }
      });      
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
          "Sr.No",
          "Member ID",
          "Name",
          "LEG A",
          "LEG B",
          "LEG C",
          "LEG D",
          "Previous CF",
          "Matched",
          "Carry Forward",
        ];
        const filterVal = [
          "id",
          "member_id",
          "name",
          "leg_1",
          "leg_2",
          "leg_3",
          "leg_4",
          "previous_carry_forward",
          "matched",
          "carry_forward",
        ];
        const data = this.formatJson(filterVal, this.list);
        excel.export_json_to_excel({
          header: tHeader,
          data,
          filename: "matching-points"
        });
        this.downloadLoading = false;
      });
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v =>
        filterVal.map(j => {
          if (j === "timestamp") {
            return parseTime(v[j]);
          } else if(j === "member_id") {
            return v.member.user.username
          }else if(j === "name") {
            return v.member.user.name
          }else {
            return v[j];
          }
        })
      );
    },
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
