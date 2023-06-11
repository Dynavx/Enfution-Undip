import AdminLayout from "@/Layouts/AdminLayout";
import { Head } from "@inertiajs/react";
import SemnasAdmin from "./Admin/SemnasAdmin";
import DetailSemnas from "./Admin/DetailSemnas";
import MainDashboard from "./Admin/MainDashboard";

export default function Dashboard(props) {
    const views = [
        <MainDashboard semnas={props.semnas} />,
        <SemnasAdmin
            trx={props.transactions}
            filter={props.filter}
            search={props.search}
            info={props.info ?? ""}
            status={props.status ?? ""}
        />,
        <DetailSemnas
            data_peserta={props.data_diri}
            page={props.page}
            event={props.event}
            status={props.status ?? ""}
        />,
    ];
    console.log(props);

    return (
        <>
            <Head title={props.title} />
            <AdminLayout
                children={views[props.selectedTable]}
                title={props.sectionTitle}
                index={props.active}
            ></AdminLayout>
        </>
    );
}
