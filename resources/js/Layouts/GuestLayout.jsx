import ApplicationLogo from '@/Components/ApplicationLogo';
import { Link } from '@inertiajs/react';

export default function Guest({ children }) {
    return (
        <div className=" hero min-h-screen bg-cover bg-center bg-no-repeat relative flex flex-col  sm:justify-center items-center pt-6 sm:pt-0"
        style={{
            backgroundImage: `url("images/hero.png")`,
        }}>
            <div className='sm:mr-10 self-center'>
                <Link href="/">
                    <ApplicationLogo className="w-20 h-20 fill-current text-gray-500" />
                </Link>
            </div>

            <div className="self-center w-full sm:max-w-md mt-0 px-6 py-4 bg-[#FFFFFF] shadow-md overflow-hidden sm:rounded-lg">
                {children}
            </div>
        </div>
    );
}
