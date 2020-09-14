@extends('admin.layouts.index')
@push('extra_script')
<script src="https://cdn.jsdelivr.net/npm/vue-tables-2@2.0.34/dist/vue-tables.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue-js-toggle-button@1.3.3/dist/index.min.js"></script>
@endpush

@section('content')
    <div id="app">
        <h2>Tables</h2>
        <v-client-table :data="filteredTables" :columns="columns" :options="options"/>
            <template slot="availability" slot-scope="props">
                <toggle-button  :width="100" :value="props.row.availability" @change="onChangeEventHandler(props.row.id)" :labels="{checked: 'Available', unchecked: 'Unavailable'}"/>
            </template>
        </v-client-table>
    </div>
@endsection

@push('scripts')
<script>
Vue.use(window["vue-js-toggle-button"].default);
Vue.use(VueTables.ClientTable)

const app = new Vue({
    el: '#app',
    data: {
        tables: [],
        columns: ['id', 'name', 'number_of_seats','availability'],
        options: {
            headings: {
                id: 'ID',
                name: 'Name',
                'number_of_seats': 'Capacity',
                availability: 'Availability Status'
            },

        }
    },
    async mounted() {
        await this.getAllTables()
    },
    methods: {
        async getAllTables(){
            try {
                const response = await axios.get(`/admin/tables/get-all`)
                this.tables = response.data.tables
            } catch (error) {
                console.log(error)
            }
        },
        async onChangeEventHandler(id) {
            try {
                const response = await axios.put(`/admin/tables/toggle-availability/${id}`)
                if (response.data.success) {
                    this.tables = this.tables.map(table => {
                        if (table.id === id) {
                            table.availability = response.data.table.availability
                        }
                        return table
                    })
                }
            } catch (error) {
                console.log(error)
            }
        }
    },
    computed: {
        filteredTables() {
                return this.tables.map(table => {
                    return {
                        ...table, 
                        availability: table.availability ? true : false,
                    }
                });
            },
    }
})
</script>
@endpush