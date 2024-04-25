import ChatLayout from '@/Layouts/ChatLayout';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

function Home({ auth }) {
    return (
        <>

        </>
    );
}

Home.layout = (page) => {
    return (
        <AuthenticatedLayout>
            <ChatLayout
                children={page}
            ></ChatLayout>
        </AuthenticatedLayout>
    )
}

export default Home
