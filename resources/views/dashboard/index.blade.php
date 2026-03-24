<x-layouts.main-content title="Departments"
                        heading="List of departments"
                        subheading="Manage the departments of the institution">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl ">
        <div class="flex justify-start ">
            <div class="my-4 p-6 ">
                <div class="flex items-center gap-4 mb-4">
                    <flux:button variant="primary" href="#">Create a new department</flux:button>
                </div>
                <div class="my-4 font-base text-sm text-gray-700 dark:text-gray-300">
                    <table class="table-auto border-collapse">
                        <thead>
                        <tr class="border-b-2 border-b-gray-400 dark:border-b-gray-500 bg-gray-100 dark:bg-gray-800">
                            <th class="px-2 py-2 text-left">Abbreviation</th>
                            <th class="px-2 py-2 text-left">Name</th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    #
                </div>
            </div>
        </div>
    </div>
</x-layouts.main-content>
