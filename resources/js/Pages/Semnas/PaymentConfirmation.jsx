import { Head, Link, router } from "@inertiajs/react";
import numeral from "numeral";
import { useEffect, useState } from "react";

const PaymentConfirmation = (props) => {
    const [account_name, setName] = useState("");
    const [account_number, setNumber] = useState("");
    const [bank_name, setBank] = useState("");
    const [payment_slip, setSlip] = useState("");
    const [modalOpen, setModalOpen] = useState(false);

    const errors = props.errors;
    const name = props.name ?? "";
    const modal = props.modal;

    const data = {
        account_name,
        account_number,
        bank_name,
        payment_slip,
    };

    const resetForm = () => {
        setName("");
        setNumber("");
        setBank("");
        setSlip("");
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        let fd = new FormData();

        for (const d in data) {
            fd.append(`${d}`, data[d]);
        }

        for (const [i, val] of fd.entries()) {
            console.log(val);
        }

        router.post("/payment-confirmation-semnas", fd);
    };

    const redirectURL = () => {
        const time = 1.1;
        redirectTime = setTimeout(() => {
            router.post("/payment-confirmation-semnas");
        }, 60 * 1000 * time);
    };

    useEffect(() => {
        // redirectURL();
        if (Object.keys(errors).length == 0 && modal) {
            resetForm();
            setModalOpen(true);
        } else {
            console.log(errors);
        }
    }, [errors, modal]);

    return (
        <div className="w-full bg-[#FFF9EE] min-h-screen pb-20">
            <Head title="Payment Confirmation - National Seminar" />
            <div className="container flex-col flex justify-center mx-auto">
                <h1 className="text-center font-semibold text-5xl mb-10 pt-20">
                    Payment Confirmation
                </h1>
                <p className="text-center font-semibold text-xl mb-3">
                    You have to pay:
                </p>

                <p className="bg-[#1E2E40] text-white text-center py-3 text-xl font-semibold mb-5 mx-10">
                    IDR {numeral(props.total).format("0,0.00")}
                </p>
                <div className="flex justify-center md:px-10 lg:px-20">
                    <div className="border-l border-t border-b border-black h-28 sm:h-40 w-80 self-center">
                        <img
                            src="images/bank/dana.svg"
                            className="w-24 sm:w-28 flex mx-auto pt-7 sm:pt-10"
                        />
                        <p className="text-[#EB9928] text-center text-sm sm:text-base">
                            Maya Simorangkir
                        </p>
                        <p className="text-[#1E2E40] text-center text-sm sm:text-base">
                            082273259386
                        </p>
                    </div>
                    <div className="border border-black h-28 sm:h-40 w-80 ">
                        <img
                            src="images/bank/bni.svg"
                            className="w-24 sm:w-28 flex mx-auto sm:pt-10 pt-6"
                        />
                        <p className="text-[#EB9928] text-center text-sm sm:text-base">
                            Maya Simorangkir
                        </p>
                        <p className="text-[#1E2E40] text-center text-sm sm:text-base">
                            1349522707
                        </p>
                    </div>
                </div>
                <form onSubmit={handleSubmit} encType="multipart/form-data">
                    <div className="grid grid-cols-1 md:grid-cols-2 mt-10">
                        <div className="md:mx-10 mb-5">
                            <p className="font-semibold text-center text-lg">
                                Name <span className="text-[#EB9928]">*</span>{" "}
                            </p>
                            <input
                                type="text"
                                className="input input-bordered w-full bg-transparent border-[#1E2E40] rounded-md focus:border-[#EB9928] focus:ring-[#EB9928]"
                                readOnly
                                value={name}
                            />
                        </div>
                        <div className="mb-5 md:mx-10">
                            <p className="font-semibold text-center text-lg">
                                Bank Name{" "}
                                <span className="text-[#EB9928]">*</span>{" "}
                            </p>
                            <input
                                type="text"
                                className="input input-bordered w-full bg-transparent border-[#1E2E40] rounded-md focus:border-[#EB9928] focus:ring-[#EB9928]"
                                onChange={(bank) => setBank(bank.target.value)}
                            />
                            {errors != null && errors.bank_name && (
                                <span className="text-red-600">
                                    {errors.bank_name}
                                </span>
                            )}
                        </div>
                        <div className="mb-5 md:mx-10">
                            <p className="font-semibold text-center text-lg">
                                Account Name
                                <span className="text-[#EB9928]">*</span>{" "}
                            </p>
                            <input
                                type="text"
                                className="input input-bordered w-full bg-transparent border-[#1E2E40] rounded-md focus:border-[#EB9928] focus:ring-[#EB9928]"
                                onChange={(acc_name) =>
                                    setName(acc_name.target.value)
                                }
                            />
                            {errors != null && errors.account_name && (
                                <span className="text-red-600">
                                    {errors.account_name}
                                </span>
                            )}
                        </div>

                        <div className="mb-5 md:mx-10">
                            <p className="font-semibold text-center text-lg">
                                Account Number{" "}
                                <span className="text-[#EB9928]">*</span>{" "}
                            </p>
                            <input
                                type="text"
                                className="input input-bordered w-full bg-transparent border-[#1E2E40] rounded-md focus:border-[#EB9928] focus:ring-[#EB9928]"
                                onChange={(acc_number) =>
                                    setNumber(acc_number.target.value)
                                }
                            />
                            {errors != null && errors.account_number && (
                                <span className="text-red-600">
                                    {errors.account_number}
                                </span>
                            )}
                        </div>
                    </div>

                    <div className="text-center pt-10">
                        <p className="font-semibold text-lg">
                            Payment Proof (with .jpg and .png formats)
                            <span className="text-[#EB9928]">*</span>
                        </p>
                        <p className="text-[#EB9928] font-semibold mb-3">
                            Payment Slip
                        </p>
                        <input
                            type="file"
                            className="file-input file-input-bordered sm:w-1/2 w-full"
                            onChange={(e) => {
                                setSlip(e.target.files[0]);
                            }}
                        />
                        {errors != null && errors.payment_slip && (
                            <span className="text-red-600 text-center block">
                                {errors.payment_slip}
                            </span>
                        )}
                    </div>

                    <div className="flex justify-center mt-20">
                        <button className="btn w-28 sm:w-48 rounded-none mx-10 bg-[#1E2E40]">
                            Submit
                        </button>
                    </div>
                </form>
            </div>

            <section>
                <div
                    className={
                        modalOpen
                            ? "fixed z-50 top-0 left-0 flex h-full min-h-screen w-full items-center justify-center bg-black bg-opacity-[75%] px-4 py-5"
                            : "hidden"
                    }
                >
                    <div
                        className="w-full h-full relative sm:scale-75 xl:scale-90 max-w-lg max-h-96 xl:max-w-[984px] xl:max-h-[561px] bg-cover bg-center bg-no-repeat py-8 md:py-20 rounded-3xl xl:rounded-none px-4 sm:px-8 text-center"
                        style={{
                            backgroundImage: `url("images/subscribe.svg")`,
                        }}
                    >
                        <h3 className="pb-2 text-2xl font-bold text-dark sm:text-5xl">
                            Thank You!
                        </h3>

                        <p className="mb-5 text-base sm:text-lg leading-relaxed text-body-color max-w-md flex mx-auto">
                            Your registration has completed. For further notice,
                            will be announced through your email.
                        </p>
                        <p className=" text-sm text-center leading-relaxed text-body-color max-w-sm flex mx-auto justify-center">
                            *we will give the link through email
                        </p>

                        <p className="mb-5 text-sm text-center leading-relaxed text-body-color max-w-sm flex mx-auto">
                            *if within 1x24 hours you don’t receive any email
                            from us, please contact us
                        </p>

                        <Link href={route("national-seminar.main")}>
                            <button className="underline italic">
                                Return to homepage
                            </button>
                        </Link>
                        <img
                            src="images/logoEnfu.svg"
                            alt=""
                            onClick={() => {
                                setModalOpen(false);
                            }}
                            className="absolute sm:right-32 sm:top-16 cursor-pointer left-5 bottom-5 w-11 sm:hidden"
                        />
                    </div>
                </div>
            </section>
        </div>
    );
};

export default PaymentConfirmation;
